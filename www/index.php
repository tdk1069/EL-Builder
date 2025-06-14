<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>MUD Area Builder - Login</title>
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
  <h2>Enter the Realm</h2>
  <form id="loginForm">
    <div class="mb-3">
      <label class="form-label">Username</label>
      <input type="text" name="username" class="form-control" required />
    </div>
    <div class="mb-3">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" required />
    </div>
    <button type="submit" class="btn btn-parchment w-100">Log In</button>
  </form>
  <p class="mt-3 text-center">No account? <a href="register.php">Register here</a></p>
</div>

<!-- Bootstrap Bundle JS (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.getElementById('loginForm').onsubmit = async (e) => {
  e.preventDefault();
  const formData = new FormData(e.target);

  // Convert FormData to plain object
  const data = {};
  formData.forEach((value, key) => data[key] = value);

  const res = await fetch('auth/login.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  });

  const result = await res.json();

  if (result.success) {
    location.href = 'dashboard.php';
  } else {
    const modalBody = document.getElementById('errorModalBody');
    modalBody.textContent = result.error || 'Unknown error';

    const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
    errorModal.show();

  }
};
</script>


<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content parchment-bg">
      <div class="modal-header">
        <h5 class="modal-title" id="errorModalLabel">Login Error</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="errorModalBody">
        An unknown error occurred.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-parchment" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

</body>
</html>
