# Futech Blog

A modern blog application built with **Laravel** (backend) and **Vue.js** (frontend).

## Features

- User authentication (register, login, forgot password)
- Blog post management (create, view, update)
- RESTful API architecture
- Vue 3 with Composition API
- Vite for frontend build tooling

## Tech Stack

**Backend:**
- Laravel 10+
- MySQL database
- JWT/Sanctum authentication

**Frontend:**
- Vue 3
- Vite
- Vue Router
- Axios

## Getting Started

### Prerequisites

- PHP 8.1+
- Composer
- Node.js 18+
- MySQL 8.0+

### Installation

1. Clone the repository
```bash
git clone https://github.com/karkirajendra/futech_Backend.git
```

2. Backend setup
```bash
cd futech_backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

3. Frontend setup
```bash
cd futech_frontend
npm install
npm run dev
```

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/register | User registration |
| POST | /api/login | User login |
| GET | /api/blogs | List all blogs |
| POST | /api/blogs | Create new blog |
| GET | /api/blogs/{id} | Get single blog |

## License

MIT License
