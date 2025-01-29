<?php
session_start();
@include 'config.php';

if (!isset($_SESSION['email'])) {
    header('Location: CusProfile.php');
    exit();
}

$email = $_SESSION['email']; 

// Query to fetch customer data based on the email
$query = "SELECT * FROM customer WHERE email = '$email'";
$result = mysqli_query($conn, $query);

// Check if the query returned any result
if ($result && mysqli_num_rows($result) > 0) {
    $lender = mysqli_fetch_assoc($result);
} else {
    $lender = null;
    $message = 'No customer found with this email address';
}

// Handle profile update form submission
if (isset($_POST['update_profile'])) {
    $firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
    $lastname = mysqli_real_escape_string($conn, $_POST['lastname']);
    $town = mysqli_real_escape_string($conn, $_POST['town']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $contact_number = mysqli_real_escape_string($conn, $_POST['contact_number']);

    // Create Cusprofile_pics directory if it doesn't exist
    $upload_dir = 'Cusprofile_pics/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Handle profile image upload
    if (!empty($_FILES['profile_image']['name'])) {
        $profile_image = $_FILES['profile_image']['name'];
        $profile_image_tmp = $_FILES['profile_image']['tmp_name'];
        $profile_image_size = $_FILES['profile_image']['size'];
        $profile_image_type = $_FILES['profile_image']['type'];

        // Generate unique filename
        $profile_image_new = uniqid() . '_' . $profile_image;
        $profile_image_path = $upload_dir . $profile_image_new;

        // Validate image
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($profile_image_type, $allowed_types)) {
            $_SESSION['message'] = 'Invalid image type. Only JPG, JPEG, and PNG are allowed.';
        } elseif ($profile_image_size > $max_size) {
            $_SESSION['message'] = 'Image size too large. Maximum size is 5MB.';
        } else {
            // Delete old profile image if it exists and isn't default
            if ($lender['profile_image'] && $lender['profile_image'] != 'default.png' && file_exists($upload_dir . $lender['profile_image'])) {
                unlink($upload_dir . $lender['profile_image']);
            }

            // Move new image
            if (move_uploaded_file($profile_image_tmp, $profile_image_path)) {
                // Update database with new image name
                $update_image = "UPDATE customer SET profile_image = ? WHERE email = ?";
                $stmt = $conn->prepare($update_image);
                $stmt->bind_param("ss", $profile_image_new, $email);
                $stmt->execute();
                $stmt->close();
            } else {
                $_SESSION['message'] = 'Failed to upload image.';
            }
        }
    }

    // Update other profile information
    $update = "UPDATE customer SET firstname = ?, lastname = ?, town = ?, 
               address = ?, contact_number = ? WHERE email = ?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("ssssss", $firstname, $lastname, $town, $address, $contact_number, $email);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = 'Profile updated successfully';
    } else {
        $_SESSION['message'] = 'Failed to update profile';
    }
    $stmt->close();

    header('Location: CusProfile.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrower Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/style.css?v=1.0">
</head>
<body>

<?php
if (isset($message)) {
    echo '<span class="message">' . htmlspecialchars($message) . '</span>';
} elseif (isset($_SESSION['message'])) {
    echo '<span class="message success">' . htmlspecialchars($_SESSION['message']) . '</span>';
    unset($_SESSION['message']);
}
?>

<div class="container">
    <div class="profile-container">
        <form action="CusProfile.php" method="post" enctype="multipart/form-data">
            <h3>Your Profile</h3>

            <!-- Display Profile Picture -->
            <div class="profile-picture">
                <?php if ($lender && !empty($lender['profile_image'])): ?>
                    <img src="Cusprofile_pics/<?php echo htmlspecialchars($lender['profile_image']); ?>" alt="Profile Picture" height="150">
                <?php else: ?>
                    <img src="Cusprofile_pics/default.png" alt="Default Profile Picture" height="150">
                <?php endif; ?>
            </div>

            <!-- Upload New Profile Picture -->
            <label for="profile_image">Upload New Profile Picture</label>
            <input type="file" name="profile_image" accept="image/png, image/jpeg, image/jpg" class="box">

            <label for="firstname">First Name <span class="required">*</span></label>
            <input type="text" name="firstname" value="<?php echo $lender ? htmlspecialchars($lender['firstname']) : ''; ?>" class="box" required>

            <label for="lastname">Last Name <span class="required">*</span></label>
            <input type="text" name="lastname" value="<?php echo $lender ? htmlspecialchars($lender['lastname']) : ''; ?>" class="box" required>

            <label for="email">Email</label>
            <input type="email" name="email" value="<?php echo $lender ? htmlspecialchars($lender['email']) : ''; ?>" class="box" disabled>

            <label for="town">Town <span class="required">*</span></label>
            <select name="town" id="town" class="box" required>
                <option value="Pototan" <?php echo ($lender && $lender['town'] == 'Pototan') ? 'selected' : ''; ?>>Pototan</option>
                <option value="Zarraga" <?php echo ($lender && $lender['town'] == 'Zarraga') ? 'selected' : ''; ?>>Zarraga</option>
            </select>

            <label for="address">Barangay <span class="required">*</span></label>
            <select name="address" id="address" class="box" required>
                <option value="<?php echo $lender ? htmlspecialchars($lender['address']) : ''; ?>" selected>
                    <?php echo $lender ? htmlspecialchars($lender['address']) : ''; ?>
                </option>
            </select>

            <label for="contact_number">Contact Number <span class="required">*</span></label>
            <input type="text" name="contact_number" value="<?php echo $lender ? htmlspecialchars($lender['contact_number']) : ''; ?>" class="box" required>

            <!-- Submit Button -->
            <input type="submit" class="btn" name="update_profile" value="Update Profile">
        </form>

        <a href="CustomerDashboard.php" class="btn" style="margin-top: 1rem;">Home</a>
    </div>
</div>

<script>
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
        'Poblacion Norte', 'Poblacion Sur', 'Talauguis', 'Tubigan',
        'Inagawan', 'Antipolo', 'Jalaud', 'Yambu'
    ]
};

function updateBarangays() {
    const townSelect = document.getElementById('town');
    const barangaySelect = document.getElementById('address');
    const currentBarangay = barangaySelect.value;
    
    barangaySelect.innerHTML = '';
    
    const barangays = barangaysByTown[townSelect.value];
    barangays.forEach(barangay => {
        const option = document.createElement('option');
        option.value = barangay;
        option.textContent = barangay;
        if (barangay === currentBarangay) {
            option.selected = true;
        }
        barangaySelect.appendChild(option);
    });
}

document.getElementById('town').addEventListener('change', updateBarangays);
updateBarangays();
</script>

</body>
</html>