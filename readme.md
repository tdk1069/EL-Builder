# MUD Area Editor

A browser-based fantasy MUD (Multi-User Dungeon) area editor with a grid-based map system, room linking, and room property editing. Built with PHP, MariaDB, and Docker.

---

## 🚀 Features

- Grid-based room editor
- Visual room linking (N/S/E/W/NE/etc.)
- Room properties: short/long descriptions, smells, items
- User authentication
- Save/load areas from MariaDB
- Dockerized setup for easy deployment

---

## 🐳 Docker Setup

This project uses Docker Compose to spin up the full environment.

### 📁 Folder Structure

- `maria_data/` – **ignored**: volume for MariaDB data
- `nginx/` – **ignored**: NGINX config
- `src/` – your application source files (e.g., PHP frontend)

### 🧰 Requirements

- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)

### ▶️ Run the project

```bash
docker compose up -d
```

Visit the editor at [http://localhost](http://localhost)

---

## 🛠️ Development

Make sure to exclude `maria_data/` and `nginx/` folders from version control — they’re local artifacts:

```gitignore
# .gitignore
maria_data/
nginx/
```

---

## 🗃️ Database

- Auto-creates MariaDB database and required tables on first run
- Stores room and area data linked to authenticated users

---

## 🧾 License

MIT License – see [`LICENSE`](LICENSE) for details.

---

[View Changelog](CHANGELOG.md)

---

## 🙏 Credits

Crafted with love for old-school text-based worlds.
