<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>MUD Area Builder - Register</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Fantasy-style font -->
  <link href="https://fonts.googleapis.com/css2?family=UnifrakturCook:wght@700&display=swap" rel="stylesheet">

  <style>
    body {
      background: url('assets/background.svg') no-repeat center center;
      background-size: cover;
      font-family: 'Georgia', serif;
      color: #3e2f1c;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .parchment-box {
      background-color: rgba(255, 248, 220, 0.95);
      border: 3px solid #7a5c3e;
      padding: 2rem;
      border-radius: 12px;
      max-width: 400px;
      width: 100%;
      box-shadow: 4px 4px 12px rgba(0, 0, 0, 0.5);
    }

    h2 {
      font-family: 'UnifrakturCook', cursive;
      text-align: center;
      font-size: 2.5rem;
    }

    .form-control {
      background-color: #fdf6e3;
      border: 1px solid #7a5c3e;
      color: #3e2f1c;
    }

    .form-control:focus {
      border-color: #5a3e2b;
      box-shadow: none;
      background-color: #fff8e7;
    }

    .btn-parchment {
      background-color: #7a5c3e;
      border: none;
      color: #fff8e7;
    }

    .btn-parchment:hover {
      background-color: #5a3e2b;
    }

    a {
      color: #5a3e2b;
      text-decoration: underline;
    }
  </style>
</head>

<body>

  <div class="parchment-box">
    <h2>Create Account</h2>
    <form id="registerForm">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" required />
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required />
      </div>
      <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="confirm" class="form-control" required />
      </div>
      <button type="submit" class="btn btn-parchment w-100">Register</button>
    </form>
    <p class="mt-3 text-center">Already registered? <a href="/index.php">Return to login</a></p>
  </div>

  <script>
    document.getElementById('registerForm').onsubmit = async (e) => {
      e.preventDefault();
      const formData = new FormData(e.target);

      const password = formData.get('password');
      const confirm = formData.get('confirm');
      if (password !== confirm) {
        alert("Passwords do not match.");
        return;
      }

      const res = await fetch('auth/register.php', {
        method: 'POST',
        body: JSON.stringify({
          username: formData.get('username'),
          password: password
        }),
        headers: { 'Content-Type': 'application/json' }
      });

      const result = await res.json();
      if (result.success) {
        location.href = 'dashboard.php';
      } else {
        alert(result.error || "Registration failed.");
      }
    };
  </script>

</body>

</html>