<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrower Login/Register</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <!-- Login Form -->
            <form id="login-form" class="form">
                <h2>LogIn</h2>
                <input type="text" id="login-email" placeholder="Email" required>
                <input type="password" id="login-password" placeholder="Password" required>
                <button type="button" id="login-button">Login</button>
                <p id="show-register">————— New User? Register Here —————</p>
                <a href="../index.php" class="btn">Back</a>
            </form>

            <!-- Registration Form -->
            <form id="register-form" class="form hidden" enctype="multipart/form-data">
                <h2>New Account</h2>
                <input type="text" id="register-firstname" name="firstname" placeholder="First Name" required>
                <input type="text" id="register-lastname" name="lastname" placeholder="Last Name" required>
                <input type="tel" id="register-contact" name="contact" placeholder="Contact Number" required maxlength="11" oninput="validateContactNumber()">
                
                <!-- Updated Address Selection -->
                <select id="register-town" name="town" required>
                    <option value="" disabled selected>Select Town</option>
                    <option value="Pototan">Pototan</option>
                    <option value="Zarraga">Zarraga</option>
                </select>

                <select id="register-address" name="address" required disabled>
                    <option value="" disabled selected>Select Barangay</option>
                </select>
                
                <input type="text" id="register-email" name="email" placeholder="Email" required>
                <input type="password" id="register-password" name="password" placeholder="Password" required>
                <input type="password" id="register-confirm-password" name="confirmPassword" placeholder="Confirm Password" required>
                <input type="file" id="register-image" name="images" accept="image/*" required>
                
                <button type="button" class="register-button" id="register-button">Register</button>
                <p id="registration-message" style="color: red;"></p>
                
                <button type="button" id="back-to-login-button">Back to Login</button>
            </form>

            <!-- Verification Code Form -->
            <form id="verification-form" class="form hidden">
                <h2>Enter Verification Code</h2>
                <input type="text" id="verification-code" placeholder="Enter the code sent to your email" required>
                <button type="button" id="verify-button">Verify Code</button>
                <p id="verification-message" style="color: red;"></p>
                <p id="back-register">——— Back to Register ———</p>
            </form>
        </div>
    </div>

    <script>
        // Barangay data by town
        const barangaysByTown = {
            'Pototan': [
                'Abangay', 'Amamaros', 'Bagacay', 'Barasan', 'Batuan', 'Bongco',
                'Cahaguichican', 'Callan', 'Cansilayan', 'Casalsagan', 'Cato-ogan',
                'Cau-ayan', 'Culob', 'Danao', 'Dapitan', 'Dawis', 'Dongsol',
                'Fernando Parcon Ward', 'Guibuangan', 'Guinacas', 'Igang', 'Intaluan',
                'Iwa Ilaud', 'Iwa Ilaya', 'Jamabalud', 'Jebioc', 'Lay-ahan',
                'Lopez Jaena Ward', 'Lumbo', 'Macatol', 'Malusgod', 'Nabitasan',
                'Naga', 'Nanga', 'Naslo', 'Pajo', 'Palanguia', 'Pitogo',
                'Primitivo Ledesma Ward', 'Purog', 'Rumbang', 'San Jose Ward',
                'Sinuagan', 'Tuburan', 'Tumcon Ilaud', 'Tumcon Ilaya', 'Ubang', 'Zarrague'
            ],
            'Zarraga': [
                'Poblacion Norte',
                'Poblacion Sur',
                'Talauguis',
                'Tubigan',
                'Inagawan',
                'Antipolo',
                'Jalaud',
                'Yambu'
            ]
        };

        let verificationCode = '';

        // Town selection handler
        document.getElementById('register-town').addEventListener('change', function() {
            const townSelect = this;
            const barangaySelect = document.getElementById('register-address');
            
            // Clear current barangay options
            barangaySelect.innerHTML = '<option value="" disabled selected>Select Barangay</option>';
            
            if (townSelect.value) {
                // Enable barangay select
                barangaySelect.disabled = false;
                
                // Add barangay options for selected town
                const barangays = barangaysByTown[townSelect.value];
                barangays.forEach(barangay => {
                    const option = document.createElement('option');
                    option.value = barangay;
                    option.textContent = barangay;
                    barangaySelect.appendChild(option);
                });
            } else {
                // Disable barangay select if no town is selected
                barangaySelect.disabled = true;
            }
        });

        document.getElementById('show-register').addEventListener('click', function() {
            document.getElementById('login-form').classList.add('hidden');
            document.getElementById('register-form').classList.remove('hidden');
        });

        document.getElementById('back-to-login-button').addEventListener('click', function() {
            document.getElementById('register-form').classList.add('hidden');
            document.getElementById('login-form').classList.remove('hidden');
            document.getElementById('register-form').reset();
        });

        document.getElementById('back-register').addEventListener('click', function() {
            document.getElementById('verification-form').classList.add('hidden');
            document.getElementById('register-form').classList.remove('hidden');
        });

        function generateVerificationCode() {
            return Math.floor(100000 + Math.random() * 900000).toString();
        }

        function validateContactNumber() {
            const contactInput = document.getElementById('register-contact');
            let value = contactInput.value;
            value = value.replace(/\D/g, '');
            if (value.length > 11) {
                value = value.slice(0, 11);
            }
            contactInput.value = value;
        }


        document.getElementById('register-contact').addEventListener('input', validateContactNumber);
        // Handle Registration Button Click
        document.getElementById('register-button').addEventListener('click', async function() {
            const firstname = document.getElementById('register-firstname').value;
            const lastname = document.getElementById('register-lastname').value;
            const contact = document.getElementById('register-contact').value;
            const town = document.getElementById('register-town').value;
            const address = document.getElementById('register-address').value;
            const email = document.getElementById('register-email').value;
            const password = document.getElementById('register-password').value;
            const confirmPassword = document.getElementById('register-confirm-password').value;
            const images = document.getElementById('register-image').files[0];

            // Validate if all fields are filled
            if (!firstname || !lastname || !contact || !town || !address || !email || !password || !confirmPassword || !images) {
                document.getElementById('registration-message').innerText = "All fields are required!";
                return;
            }

            // Check if passwords match
            if (password !== confirmPassword) {
                document.getElementById('registration-message').innerText = "Passwords do not match!";
                return;
            }

            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                document.getElementById('registration-message').innerText = "Please enter a valid email address!";
                return;
            }

            // Check for valid contact number (11 digits and starts with 09)
            if (!/^\d{11}$/.test(contact) || !contact.startsWith('09')) {
                document.getElementById('registration-message').innerText = "Please enter a valid 11-digit contact number starting with 09!";
                return;
            }


            try {
                const checkEmailResponse = await fetch('check_email.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ email: email })
                });

                const emailCheck = await checkEmailResponse.json();
                
                if (emailCheck.exists) {
                    document.getElementById('registration-message').innerText = "This email is already registered!";
                    return;
                }

                // If email doesn't exist, proceed with registration
                verificationCode = generateVerificationCode();
                await sendVerificationEmail(email, verificationCode);
                document.getElementById('register-form').classList.add('hidden');
                document.getElementById('verification-form').classList.remove('hidden');
                
            } catch (error) {
                console.error('Error checking email:', error);
                document.getElementById('registration-message').innerText = "An error occurred. Please try again.";
            }
            verificationCode = generateVerificationCode();
            await sendVerificationEmail(email, verificationCode);
            document.getElementById('register-form').classList.add('hidden');
            document.getElementById('verification-form').classList.remove('hidden');
        });

        async function sendVerificationEmail(email, code) {
            try {
                const response = await fetch('send_verification_email.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ email: email, code: code })
                });

                const data = await response.json();
                if (data.success) {
                    console.log("Verification email sent!");
                } else {
                    throw new Error(data.message || "Failed to send verification email");
                }
            } catch (error) {
                console.error('Error:', error);
                alert("Failed to send verification code: " + error.message);
            }
        }

        document.getElementById('verify-button').addEventListener('click', function() {
            const enteredCode = document.getElementById('verification-code').value;
            if (enteredCode === verificationCode) {
                completeRegistration();
            } else {
                document.getElementById('verification-message').innerText = "Invalid verification code!";
            }
        });

        async function completeRegistration() {
            const firstname = document.getElementById('register-firstname').value;
            const lastname = document.getElementById('register-lastname').value;
            const contact = document.getElementById('register-contact').value;
            const town = document.getElementById('register-town').value;
            const address = document.getElementById('register-address').value;
            const email = document.getElementById('register-email').value;
            const password = document.getElementById('register-password').value;
            const images = document.getElementById('register-image').files[0];

            const formData = new FormData();
            formData.append('firstname', firstname);
            formData.append('lastname', lastname);
            formData.append('contact', contact);
            formData.append('town', town);
            formData.append('address', address);
            formData.append('email', email);
            formData.append('password', password);
            formData.append('images', images);

            try {
                const response = await fetch('CusReg.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                if (data.success) {
                    alert("Registration successful!");
                    window.location.href = 'CustomerMain.php';
                } else {
                    alert("Registration failed: " + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert("An error occurred during registration.");
            }
        }

        document.getElementById('login-button').addEventListener('click', function() {
            const email = document.getElementById('login-email').value;
            const password = document.getElementById('login-password').value;

            const formData = new URLSearchParams();
            formData.append('email', email);
            formData.append('password', password);

            fetch('CusLogin.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.href = 'CustomerDashboard.php';
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred during login.');
            });
        });
    </script>
</body>
</html>