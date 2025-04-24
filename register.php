<?php
session_start();
require_once 'koneksi.php'; // Pastikan Anda sudah memiliki file koneksi database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST['username'];
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];

  $errors = [];

  if (empty($username)) {
      $errors[] = "Username tidak boleh kosong.";
  }

  // Memastikan password dan mengkonfirmasi password apakah sudah cocok atau belum
  if ($password !== $confirm_password) {
      $errors[] = "Konfirmasi password tidak cocok.";
  }

  // Ngecek username apakah sudah ada 
  $query_username = "SELECT * FROM users WHERE username = ?";
  $stmt = $conn->prepare($query_username);
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows > 0) {
      $errors[] = "username sudah digunakan.";
  }

  // ngesave pengguna kalo gak ada salah
  if (empty($errors)) {
      // Hash password
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);

      // menyimpan data pengguna ke tabel user
      $query = "INSERT INTO users (username,password) VALUES (?,?)";
      $stmt = $conn->prepare($query);
      $stmt->bind_param("ss", $username, $hashed_password);
      
      if ($stmt->execute()) {
          header("Location: index.php"); // Redirect ke halaman login setelah registrasi berhasil
          exit();
      } else {
          $errors[] = "Gagal menyimpan data pengguna.";
      }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register</title>

    <style>
      @import url("https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap");
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: "Poppins", sans-serif;
      }
      html,
      body {
        display: grid;
        height: 100%;
        width: 100%;
        place-items: center;
        background: #f2f2f2;
      }
      .wrapper {
        width: 380px;
        background: #fff;
        border-radius: 15px;
        box-shadow: 0px 15px 20px rgba(0, 0, 0, 0.1);
      }
      .wrapper .title {
        font-size: 35px;
        font-weight: 600;
        text-align: center;
        line-height: 100px;
        color: #fff;
        background: linear-gradient(109.6deg, rgb(120, 143, 251) 11.2%, rgb(133, 235, 255) 91.1%);
        border-radius: 15px 15px 0 0;
      }
      .wrapper form {
        padding: 10px 30px 50px 30px;
      }
      .wrapper form .field {
        height: 50px;
        width: 100%;
        margin-top: 20px;
        position: relative;
      }
      .wrapper form .field input {
        height: 100%;
        width: 100%;
        outline: none;
        font-size: 17px;
        padding-left: 20px;
        border: 1px solid lightgrey;
        border-radius: 25px;
        transition: all 0.3s ease;
      }
      .wrapper form .field input:focus,
      form .field input:valid {
        border-color: #4158d0;
      }
      .wrapper form .field label {
        position: absolute;
        top: 50%;
        left: 20px;
        color: #999999;
        font-weight: 400;
        font-size: 17px;
        pointer-events: none;
        transform: translateY(-50%);
        transition: all 0.3s ease;
      }
      form .field input:focus ~ label,
      form .field input:valid ~ label {
        top: 0%;
        font-size: 16px;
        color: #4158d0;
        background: #fff;
        transform: translateY(-50%);
      }
      form .field input[type="submit"] {
        color: #fff;
        border: none;
        padding-left: 0;
        margin-top: -10px;
        font-size: 20px;
        font-weight: 500;
        cursor: pointer;
        background: linear-gradient(109.6deg, rgb(120, 143, 251) 11.2%, rgb(133, 235, 255) 91.1%);
        transition: all 0.3s ease;
      }
      form .signup-link {
        color: #262626;
        margin-top: 20px;
        text-align: center;
      }
      form .pass-link a,
      form .signup-link a {
        color: #4158d0;
        text-decoration: none;
      }
      form .pass-link a:hover,
      form .signup-link a:hover {
        text-decoration: underline;
      }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="title">Register Form</div>
        <?php if (!empty($errors)): ?>
            <div>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form method="post" action="">
          <div class="field">
          <input type="text" name="username" placeholder="username" required>

          </div>
            <div class="field">
                <input type="password" name="password" required />
                <label>Password</label>
            </div>
            <div class="field">
                <input type="password" name="confirm_password" required />
                <label>Confirm Password</label>
            </div>
            <div class="field">
                <input type="submit" value="Register" />
            </div>
            <div class="signup-link">
                Already have an account? <a href="index.php">Login here</a>
            </div>
        </form>
    </div>
</body>
</html>
