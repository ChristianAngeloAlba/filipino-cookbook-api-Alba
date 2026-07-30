<<<<<<< HEAD
# Filipino Cookbook API

A RESTful API developed using **PHP**, **Slim Framework**, and **MySQL** that provides information about Filipino dishes, including their categories, origins, ingredients, and cooking instructions.

This project was developed as part of the API Development Activity.

---

# Features

- Retrieve all Filipino foods
- Retrieve a specific food by ID
- Search foods by name
- View all food categories
- View all ingredients
- Add a new food
- Delete a food
- Bearer Token Authentication
- Prepared SQL Statements
- Input Validation
- Duplicate Food Validation
- JSON Responses
- Proper HTTP Status Codes

---

# Technologies Used

- PHP 8.x
- Slim Framework 4
- MySQL / MariaDB
- Composer
- XAMPP
- JSON

---

# Project Structure

```
filipino-cookbook-api/
│
├── app/
├── public/
│   └── index.php
├── vendor/
├── composer.json
├── README.md
└── database/
```

---

# Installation

## 1. Clone the repository

```bash
git clone https://github.com/<your-github-username>/filipino-cookbook-api.git
```

## 2. Navigate to the project

```bash
cd filipino-cookbook-api
```

## 3. Install dependencies

```bash
composer install
```

## 4. Import the database

Import the provided SQL file into MySQL using phpMyAdmin.

Database name:

```
filipino_cookbook_api
```

---

## 5. Configure XAMPP

Place the project inside:

```
htdocs/
```

Example:

```
C:\xampp\htdocs\filipino-cookbook-api
```

---

## 6. Start Apache and MySQL

Open the XAMPP Control Panel and start:

- Apache
- MySQL

---

# Authentication

All `/api` endpoints require a Bearer Token.

Header:

```
Authorization: Bearer dmmmsu-cookbook-token-2026
```

Requests without a valid token will return:

```json
{
    "status":"error",
    "message":"Unauthorized access. Valid API token is required."
}
```

Status Code:

```
401 Unauthorized
```

---

# API Endpoints

## Welcome

| Method | Endpoint | Description |
|---------|----------|-------------|
| GET | / | Welcome page |

---

## Foods

| Method | Endpoint | Description |
|---------|----------|-------------|
| GET | /api/foods | Get all foods |
| GET | /api/foods/{id} | Get a specific food |
| GET | /api/foods/search/{name} | Search food by name |
| POST | /api/foods | Add a new food |
| DELETE | /api/foods/{id} | Delete a food |

---

## Categories

| Method | Endpoint | Description |
|---------|----------|-------------|
| GET | /api/categories | Get all categories |

---

## Ingredients

| Method | Endpoint | Description |
|---------|----------|-------------|
| GET | /api/ingredients | Get all ingredients |

---

# Sample Request

```
GET /api/foods
```

Header

```
Authorization: Bearer dmmmsu-cookbook-token-2026
```

---

# Sample Response

```json
[
    {
        "food_id":1,
        "food_name":"Adobo",
        "category_name":"Main Course",
        "origin_name":"Luzon",
        "instructions":"...",
        "ingredients":[
            "Chicken",
            "Soy Sauce",
            "Garlic"
        ]
    }
]
```

---

# HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | OK |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 404 | Not Found |
| 409 | Conflict |
| 500 | Internal Server Error |

---

# Error Handling

The API returns JSON-formatted error messages.

Example:

```json
{
    "status":"error",
    "message":"Food not found"
}
```

---

# Security Features

The API includes the following security enhancements:

- Bearer Token Authentication
- Prepared SQL Statements
- Input Validation
- Duplicate Record Validation
- Secure JSON Error Responses
- Restricted Access to API Endpoints

---

# Optional API Enhancements

## Added Endpoints

### Search Food by Name

**Endpoint**

```
GET /api/foods/search/{name}
```

**Purpose**

Allows users to search for Filipino dishes using a complete or partial food name.

---

### Get Food Details

**Endpoint**

```
GET /api/foods/{id}
```

**Purpose**

Returns the complete details of a selected Filipino dish, including its ingredients.

---

### Category Endpoint

**Endpoint**

```
GET /api/categories
```

**Purpose**

Returns all available food categories.

---

### Ingredients Endpoint

**Endpoint**

```
GET /api/ingredients
```

**Purpose**

Returns all ingredients stored in the database.

---

## Security Improvements Implemented

- Bearer Token Authentication
- Prepared SQL Statements
- Input Validation
- Duplicate Food Validation
- Secure Error Handling

---

## Files Modified

```
public/index.php
README.md
```

---

## Testing Instructions

### 1. Start Apache and MySQL

### 2. Open Thunder Client

### 3. Add Authorization Header

```
Authorization: Bearer dmmmsu-cookbook-token-2026
```

### 4. Test the following endpoints

```
GET /api/foods
```

Expected Result

```
200 OK
```

---

```
GET /api/foods/search/adobo
```

Expected Result

```
200 OK
```

---

```
GET /api/categories
```

Expected Result

```
200 OK
```

---

```
GET /api/ingredients
```

Expected Result

```
200 OK
```

---

```
GET /api/foods/999
```

Expected Result

```
404 Not Found
```

---

```
POST /api/foods
```

Using an existing food name should return:

```
409 Conflict
```

---

```
GET /api/foods
```

Without a Bearer Token:

```
401 Unauthorized
```

---

# Author

**Name:** Christian Angelo A. Alba

**Course:** BS Information Technology

**Subject:** System Integration and Architecture 2
=======
# filipino-cookbook-api-Alba
Filipimo Cookbook API
>>>>>>> 806d96dd246c7a6d8cc16172bb8aeb8941af26af
