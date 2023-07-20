from flask import Flask, render_template, Response
import cv2
import numpy as np
from keras.models import model_from_json
import face_recognition
from face_recognition import face_encodings
import threading
from concurrent.futures import ThreadPoolExecutor
import pymysql.cursors

emotion_dict = {0: "Angry", 1: "Disgusted", 2: "Fearful", 3: "Happy", 4: "Neutral", 5: "Sad", 6: "Surprised"}

# Initialize the person emotion dictionary
person_emotion_dict = {}

# Lock for thread synchronization
lock = threading.Lock()

# Initialize models and data outside the main thread
emotion_model = None
known_face_encodings = None
known_face_names = None
face_detector = None
cap = None

def initialize_models():
    # Load the emotion recognition model
    # load json and create model
    json_file = open('model/emotion_model.json', 'r')
    loaded_model_json = json_file.read()
    json_file.close()
    emotion_model = model_from_json(loaded_model_json)

    # load weights into new model
    emotion_model.load_weights("model/emotion_model.h5")
    print("Loaded model from disk")
    return emotion_model


def initialize_known_faces_from_database():
    # Connect to the database
    connection = pymysql.connect(
        host='localhost',
        user='root',
        password='',
        db='students',
        charset='utf8mb4',
        cursorclass=pymysql.cursors.DictCursor
    )
    mycursor = connection.cursor()

    # Reset Attendance
    att = "UPDATE info SET Attendance = 'Absent'"
    reset = "UPDATE info SET Emotion = ''"
    mycursor.execute(att)
    mycursor.execute(reset)
    connection.commit()
    print(mycursor.rowcount, "Attendance and Emotion Reset")

    try:
        # Fetch the known faces from the database
        with connection.cursor() as cursor:
            sql = "SELECT Image, Name FROM info"
            cursor.execute(sql)
            results = cursor.fetchall()

            known_face_encodings = []
            known_face_names = []

            # Process each fetched record
            for row in results:
                person_id = row['Name']
                image_data = row['Image']

                # Convert the image data to a numpy array
                nparr = np.frombuffer(image_data, np.uint8)
                person_image = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

                # Compute the face encoding for the person's image
                person_encoding = face_encodings(person_image)[0]

                known_face_encodings.append(person_encoding)
                known_face_names.append(person_id)

    finally:
        # Close the database connection
        connection.close()

    return known_face_encodings, known_face_names



def initialize_face_detector():
    # Initialize the face detection model
    face_detector = cv2.CascadeClassifier('haarcascades/haarcascade_frontalface_default.xml')
    return face_detector


def recognize_faces_in_frame(frame_number, frame):
    global person_emotion_dict, known_face_encodings, known_face_names

    # Convert the frame to grayscale
    gray_frame = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)

    # Detect faces in the frame
    face_locations = face_recognition.face_locations(frame)
    for (top, right, bottom, left) in face_locations:
        # Draw a bounding box around the face
        cv2.rectangle(frame, (left, top - 50), (right, bottom + 10), (0, 255, 0), 4)

        # Preprocess the face for emotion prediction
        roi_gray_frame = gray_frame[top:bottom, left:right]
        cropped_img = np.expand_dims(np.expand_dims(cv2.resize(roi_gray_frame, (48, 48)), -1), 0)

        # Predict the emotion of the face
        emotion_prediction = emotion_model.predict(cropped_img)
        maxindex = int(np.argmax(emotion_prediction))

        # Recognize the person in the face
        face_encoding = face_recognition.face_encodings(frame, [(top, right, bottom, left)])[0]
        matches = face_recognition.compare_faces(known_face_encodings, face_encoding)
        if True in matches:
            name = known_face_names[matches.index(True)]

            with lock:
                # Check if the person is already present in the person_emotion_dict
                if name in person_emotion_dict:
                    # Get the last frame number at which the person's emotion was updated
                    last_frame_number = person_emotion_dict[name]['frame']

                    # Calculate the number of frames that have passed since the person's emotion was updated
                    num_frames_elapsed = frame_number - last_frame_number

                    # Update the person's emotion only if more than 60 frames have passed since their emotion was updated
                    if num_frames_elapsed > 60:
                        person_emotion_dict[name] = {'emotion': emotion_dict[maxindex], 'frame': frame_number}

                        # Display the name and emotion of the person
                        cv2.putText(frame, name, (left + 5, top - 59), cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 0, 255), 2,
                                    cv2.LINE_AA)
                        cv2.putText(frame, emotion_dict[maxindex], (left + 5, top - 20), cv2.FONT_HERSHEY_SIMPLEX, 1,
                                    (255, 0, 0), 2,
                                    cv2.LINE_AA)

                    else:
                        # Display the name and emotion of the person
                        cv2.putText(frame, name, (left + 5, top - 59), cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 0, 255), 2,
                                    cv2.LINE_AA)
                        cv2.putText(frame, emotion_dict[maxindex], (left + 5, top - 20), cv2.FONT_HERSHEY_SIMPLEX, 1,
                                    (255, 0, 0), 2,
                                    cv2.LINE_AA)

                else:
                    # Add the person to the person_emotion_dict
                    person_emotion_dict[name] = {'emotion': emotion_dict[maxindex], 'frame': frame_number}

                    # Attendance
                    connection = pymysql.connect(
                        host='localhost',
                        user='root',
                        password='',
                        db='students',
                        charset='utf8mb4',
                        cursorclass=pymysql.cursors.DictCursor
                    )
                    mycursor = connection.cursor()
                    up = f"UPDATE info SET Attendance = 'Present', Emotion = '{person_emotion_dict[name]['emotion']}' WHERE Name = '{name}'"
                    mycursor.execute(up)
                    connection.commit()
                    print(name + " attendance recorded")
                    connection.close()
        else:
            name = "Unknown"

        # Display the name and emotion of the person
        cv2.putText(frame, name, (left + 5, top - 59), cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 0, 255), 2, cv2.LINE_AA)
        cv2.putText(frame, emotion_dict[maxindex], (left + 5, top - 20), cv2.FONT_HERSHEY_SIMPLEX, 1, (255, 0, 0), 2,
                    cv2.LINE_AA)

    return frame



def async_face_recognition():
    global cap, emotion_model, known_face_encodings, known_face_names, face_detector

    frame_number = 0
    prev_frame = None

    with ThreadPoolExecutor() as executor:
        while True:
            ret, frame = cap.read()
            if not ret:
                break
            frame = cv2.resize(frame, (720, 480))

            # Apply motion detection
            gray_frame = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
            gray_frame = cv2.GaussianBlur(gray_frame, (21, 21), 0)

            if prev_frame is not None:
                frame_diff = cv2.absdiff(prev_frame, gray_frame)
                thresh = cv2.threshold(frame_diff, 25, 255, cv2.THRESH_BINARY)[1]
                thresh = cv2.dilate(thresh, None, iterations=2)
                contours, _ = cv2.findContours(thresh.copy(), cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)

                # Check if motion is detected
                motion_detected = False
                for contour in contours:
                    if cv2.contourArea(contour) > 500:
                        motion_detected = True
                        break

                if motion_detected:
                    # Submit the frame processing task to the executor
                    future = executor.submit(recognize_faces_in_frame, frame_number, frame)

                    # Get the result of the task (if needed)
                    result_frame = future.result()

                    # Convert the image to JPEG format to stream to the web page
                    ret, jpeg = cv2.imencode('.jpg', result_frame)

                    # Yield the frame and update the web page
                    yield (b'--frame\r\n'
                           b'Content-Type: image/jpeg\r\n\r\n' + jpeg.tobytes() + b'\r\n')

            else:
                prev_frame = gray_frame

    cap.release()


app = Flask(__name__)

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/home/')
def login():
    return render_template('home.html')

@app.route('/video_feed')
def video_feed():
    return Response(async_face_recognition(),
                    mimetype='multipart/x-mixed-replace; boundary=frame')


if __name__ == '__main__':
    # Initialize models and data outside the main thread
    emotion_model = initialize_models()
    known_face_encodings, known_face_names = initialize_known_faces_from_database()
    face_detector = initialize_face_detector()

    # Start the webcam feed
    cap = cv2.VideoCapture(0)
    cap.set(cv2.CAP_PROP_FRAME_WIDTH, 2048)
    cap.set(cv2.CAP_PROP_FRAME_HEIGHT, 1440)
    cap.set(cv2.CAP_PROP_FPS, 30)

    app.run(debug=False, host='0.0.0.0')
