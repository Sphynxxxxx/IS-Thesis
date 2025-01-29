// Show Registration Form
document.getElementById('show-register').onclick = function () {
    document.getElementById('login-form').classList.add('hidden');
    document.getElementById('register-form').classList.remove('hidden');
};

// Back to Login from Registration Form
document.getElementById('back-to-login-button').onclick = function () {
    document.getElementById('register-form').classList.add('hidden');
    document.getElementById('login-form').classList.remove('hidden');
};

// Show Forgot Password Form
document.getElementById('forgot-password-link').onclick = function () {
    document.getElementById('login-form').classList.add('hidden');
    document.getElementById('forgot-password-form').classList.remove('hidden');
};

// Back to Login from Forgot Password Form
document.getElementById('back-to-login-from-forgot').onclick = function () {
    document.getElementById('forgot-password-form').classList.add('hidden');
    document.getElementById('login-form').classList.remove('hidden');
};

// Handle Forgot Password
document.getElementById('forgot-password-submit').onclick = async function () {
    const email = document.getElementById('forgot-password-email').value;

    if (!email) {
        document.getElementById('forgot-password-message').innerText = "Email is required!";
        return;
    }

    const response = await fetch('forgot_password.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ email }),
    });

    const data = await response.json();
    if (data.success) {
        alert("A reset link has been sent to your email.");
        document.getElementById('forgot-password-form').classList.add('hidden');
        document.getElementById('login-form').classList.remove('hidden');
    } else {
        document.getElementById('forgot-password-message').innerText = data.message || "Failed to send reset link.";
    }
};
