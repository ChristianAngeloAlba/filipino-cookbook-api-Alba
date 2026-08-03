# Filipino Cookbook API

A RESTful API developed using **PHP**, **Slim Framework**, and **MySQL** that provides information about Filipino dishes, including their categories, origins, ingredients, and cooking instructions.

This project was developed as part of the **API Development Activity**.

---

# Features

* Retrieve all Filipino foods
* Retrieve a specific food by ID
* Search foods by name
* Search foods by category
* Search foods by origin
* Search foods by ingredient
* Global search across foods, categories, origins, and ingredients
* Advanced search using multiple parameters
* View all food categories
* View all ingredients
* Add a new food
* Delete a food
* Bearer Token Authentication
* Prepared SQL Statements
* Input Validation
* Duplicate Food Validation
* JSON Responses
* Proper HTTP Status Codes

---

# Technologies Used

* PHP 8.x
* Slim Framework 4
* MySQL / MariaDB
* Composer
* XAMPP
* JSON

---

# Project Structure

```text
filipino-cookbook-api/
│
├── app/
├── public/
│   └── index.php
├── vendor/
├── composer.json
├── composer.lock
├── README.md
└── .gitignore
```

---

# Installation

## 1. Clone the repository

```bash
git clone https://github.com/ChristianAngeloAlba/filipino-cookbook-api-Alba.git
```

## 2. Navigate to the project

```bash
cd filipino-cookbook-api-Alba
```

## 3. Install dependencies

```bash
composer install
```

## 4. Import the database

Import the provided SQL file into **phpMyAdmin**.

Database name:

```text
filipino_cookbook_api
```

## 5. Configure XAMPP

Place the project inside:

```text
D:\xampp\htdocs\
```

Example:

```text
D:\xampp\htdocs\filipino-cookbook-api
```

## 6. Start Apache and MySQL

Open the XAMPP Control Panel and start:

* Apache
* MySQL

## 7. Open the API

```text
http://localhost/filipino-cookbook-api/public
```

---

# Base URL

```text
http://localhost/filipino-cookbook-api/public
```

---

# Authentication

All `/api` endpoints require a **Bearer Token**.

Header:

```text
Authorization: Bearer dmmmsu-cookbook-token-2026
```

Requests without a valid token will return:

```json
{
    "status": "error",
    "message": "Unauthorized access. Valid API token is required."
}
```

HTTP Status Code:

```text
401 Unauthorized
```

---

# API Endpoints

## Public Endpoint

| Method | Endpoint | Description  |
| ------ | -------- | ------------ |
| GET    | /        | Welcome page |

---

## Food Endpoints

| Method | Endpoint                                   | Description                                     |
| ------ | ------------------------------------------ | ----------------------------------------------- |
| GET    | /api/foods                                 | Retrieve all Filipino foods                     |
| GET    | /api/foods/{id}                            | Retrieve a specific food by ID                  |
| GET    | /api/foods/search/{name}                   | Search foods by name                            |
| GET    | /api/foods/by-category/{category_name}     | Retrieve foods by category                      |
| GET    | /api/foods/by-origin/{origin_name}         | Retrieve foods by origin                        |
| GET    | /api/foods/by-ingredient/{ingredient_name} | Retrieve foods containing a specific ingredient |
| GET    | /api/foods/advanced-search                 | Advanced search using query parameters          |
| POST   | /api/foods                                 | Add a new food                                  |
| DELETE | /api/foods/{id}                            | Delete a food                                   |

---

## Global Search

| Method | Endpoint           | Description                                                       |
| ------ | ------------------ | ----------------------------------------------------------------- |
| GET    | /api/search/{term} | Search foods, categories, origins, and ingredients simultaneously |

---

## Categories

| Method | Endpoint        | Description                  |
| ------ | --------------- | ---------------------------- |
| GET    | /api/categories | Retrieve all food categories |

---

## Ingredients

| Method | Endpoint         | Description              |
| ------ | ---------------- | ------------------------ |
| GET    | /api/ingredients | Retrieve all ingredients |

---

# Sample Requests

### Retrieve All Foods

```http
GET /api/foods
```

### Search Food by Name

```http
GET /api/foods/search/adobo
```

### Search by Category

```http
GET /api/foods/by-category/Main Course
```

### Search by Origin

```http
GET /api/foods/by-origin/Luzon
```

### Search by Ingredient

```http
GET /api/foods/by-ingredient/Garlic
```

### Global Search

```http
GET /api/search/adobo
```

### Advanced Search

```http
GET /api/foods/advanced-search?food_name=adobo&category=Main Course&origin=Luzon&ingredient=Garlic
```

Header:

```text
Authorization: Bearer dmmmsu-cookbook-token-2026
```

---

# Sample Response

```json
[
    {
        "food_id": 1,
        "food_name": "Adobo",
        "category_name": "Main Course",
        "origin_name": "Luzon",
        "instructions": "...",
        "ingredients": [
            "Chicken",
            "Soy Sauce",
            "Garlic"
        ]
    }
]
```

---

# HTTP Status Codes

| Code | Description           |
| ---- | --------------------- |
| 200  | OK                    |
| 201  | Created               |
| 400  | Bad Request           |
| 401  | Unauthorized          |
| 404  | Not Found             |
| 409  | Conflict              |
| 500  | Internal Server Error |

---

# Error Handling

All errors are returned in JSON format.

Example:

```json
{
    "status": "error",
    "message": "Food not found"
}
```

---

# Security Features

The API includes the following security enhancements:

* Bearer Token Authentication
* Prepared SQL Statements
* Input Validation
* Duplicate Food Validation
* Secure JSON Error Responses
* Protected API Routes
* Parameter Validation

---

# Optional API Enhancements

## Description

The original Filipino Cookbook API was enhanced by adding several search endpoints and improving the security and validation of the API.

---

## Purpose

The enhancements make the API more flexible by allowing users to search recipes using different criteria while maintaining secure access through authentication and prepared SQL statements.

---

## Files Modified

* public/index.php
* README.md

---

## Endpoints Added

| Method | Endpoint                                   |
| ------ | ------------------------------------------ |
| GET    | /api/search/{term}                         |
| GET    | /api/foods/by-category/{category_name}     |
| GET    | /api/foods/by-origin/{origin_name}         |
| GET    | /api/foods/by-ingredient/{ingredient_name} |
| GET    | /api/foods/advanced-search                 |

---

## Security Features Implemented

* Bearer Token Authentication
* Prepared SQL Statements
* Input Validation
* Duplicate Food Validation
* Secure JSON Error Handling
* Protected API Routes

---

## Instructions for Testing

1. Start Apache and MySQL using XAMPP.
2. Import the `filipino_cookbook_api` database.
3. Open Thunder Client or Postman.
4. Add the request header:

```text
Authorization: Bearer dmmmsu-cookbook-token-2026
```

5. Test the following endpoints:

```http
GET /api/foods
GET /api/foods/{id}
GET /api/foods/search/adobo
GET /api/search/adobo
GET /api/foods/by-category/Main Course
GET /api/foods/by-origin/Luzon
GET /api/foods/by-ingredient/Garlic
GET /api/foods/advanced-search?ingredient=Garlic
GET /api/categories
GET /api/ingredients
POST /api/foods
DELETE /api/foods/{id}
```

6. Verify that:

   * Successful requests return **200 OK**
   * New food creation returns **201 Created**
   * Missing required fields return **400 Bad Request**
   * Invalid token returns **401 Unauthorized**
   * Non-existent records return **404 Not Found**
   * Duplicate food names return **409 Conflict**

---

## Screenshots

Include screenshots showing:

* Welcome Endpoint
* Get All Foods
* Get Food by ID
* Search Food by Name
* Search by Category
* Search by Origin
* Search by Ingredient
* Global Search
* Advanced Search
* Get Categories
* Get Ingredients
* Add Food
* Delete Food
* Unauthorized Request (401)
* Duplicate Food Validation (409)

---

# Developer Information

**Name:** Christian Angelo A. Alba

**Course:** Bachelor of Science in Information Technology

**Subject:** System Integration and Architecture 2

**Framework:** Slim Framework 4

**Language:** PHP 8.x

**Database:** MySQL
