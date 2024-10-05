<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Protected Page</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin-top: 50px; }
        #password-container { margin: 20px; }
        #result { margin-top: 20px; }
        #score { margin-top: 20px; font-weight: bold; }
        #success-iframe { display: none; margin-top: 30px; width: 80%; height: 600px; border: 1px solid #ccc; }
    </style>
</head>
<body>
    <h1>Password Protected Page</h1>
    <div id="password-container">
        <input type="password" id="password" placeholder="Enter Password">
        <button onclick="startAutoGuess()">Auto Guess</button>
    </div>
    <div id="result"></div>
    <div id="score">Number of Attempts: 0</div>
    <iframe id="success-iframe"></iframe>

    <script>
        let wordList = []; // Placeholder for word list loaded from wordlist.txt
        let currentWordIndex = 0;
        let isLocked = true;
        let attemptCount = 0; // Score variable to track the number of attempts

        // Function to update the score display
        function updateScore() {
            document.getElementById('score').innerText = 'Number of Attempts: ' + attemptCount;
        }

        // Function to submit a password to the PHP script
        function submitPassword(password = null) {
            let xhr = new XMLHttpRequest();
            xhr.open('POST', 'password-check.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

            let passwordToCheck = password ? password : document.getElementById('password').value;

            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    let response = JSON.parse(xhr.responseText);
                    if (response.locked === false) {
                        // Open an iframe to Google upon success
                        let iframe = document.getElementById('success-iframe');
                        iframe.style.display = 'block';
                        iframe.src = 'https://www.google.com';
                        document.getElementById('result').innerText = 'Password Correct: ' + passwordToCheck;
                        isLocked = false;
                    } else {
                        document.getElementById('result').innerText = 'Incorrect Password: ' + passwordToCheck;
                    }
                }
            };
            xhr.send('password=' + encodeURIComponent(passwordToCheck));
        }

        // Function to simulate input change and submit password for each word in the word list
        function startAutoGuess() {
            if (isLocked) {
                let passwordInput = document.getElementById('password');

                let interval = setInterval(() => {
                    if (currentWordIndex < wordList.length && isLocked) {
                        passwordInput.value = wordList[currentWordIndex]; // Set the input value to the next password in the list

                        // Increment the attempt count and update the score
                        attemptCount++;
                        updateScore();

                        // Trigger a change in the input field and submit the password
                        let event = new Event('input'); // Create an input event
                        passwordInput.dispatchEvent(event); // Dispatch the input event to trigger the submission

                        submitPassword(passwordInput.value); // Submit the password
                        currentWordIndex++; // Move to the next password in the list
                    } else {
                        clearInterval(interval); // Stop the interval when done
                        document.getElementById('result').innerText = 'All Passwords Attempted';
                    }
                }, 50); // Adjust the interval timing as needed (50ms is set for rapid guessing)
            }
        }

        // Add event listener to the password input field to attempt password submission on every change
        document.getElementById('password').addEventListener('input', () => {
            if (isLocked) {
                submitPassword(); // Attempt to submit the password on each input change
            }
        });

        // Load the word list from the server
        fetch('wordlist.txt')
            .then(response => response.text())
            .then(text => {
                wordList = text.split('\n').filter(Boolean); // Split by line and filter out empty lines
                console.log('Word list loaded:', wordList);
            });
    </script>
</body>
</html>
