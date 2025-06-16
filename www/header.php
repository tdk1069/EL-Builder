<!-- header.php -->
<link href="https://fonts.googleapis.com/css2?family=UnifrakturCook&display=swap" rel="stylesheet">
<style>
  .mud-header-wrapper {
    margin: 0;
    padding: 0;
  }

  .mud-header {
    background-color: rgba(255, 248, 220, 0.95);
    border-bottom: 2px solid #7a5c3e;
    box-shadow: 0 4px 6px rgba(0,0,0,0.2);
    padding: 1rem;
    margin: 0;
    font-family: 'Georgia', serif;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .mud-header-title {
    font-family: 'UnifrakturCook', cursive;
    font-size: 2rem;
    color: #3e2f1c;
    margin: 0;
  }

  .mud-header-nav a {
    margin: 0 1rem;
    text-decoration: none;
    color: #3e2f1c;
    font-weight: bold;
    transition: color 0.3s ease;
  }

  .mud-header-nav a:hover {
    color: #5a3e2b;
    text-decoration: underline;
  }

  /* Prevent overlap or spacing issues */
  body {
    margin-top: 0;
  }
</style>

<!-- header.php -->
<div class="mud-header-wrapper">
  <header class="mud-header">
    <div class="mud-header-title">
      <img src="assets/background.svg" height="80px" alt="Logo" style="vertical-align: middle; margin-right: 8px;">
      El'Builder
    </div>
    <nav class="mud-header-nav">
      <a href="dashboard.php">Dashboard</a>
      <a href="object.php">Objects</a>
      <a href="monsters.php">Monsters</a>
    </nav>
  </header>
</div>
