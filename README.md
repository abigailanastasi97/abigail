# Kahuna Product Registration API

## Project Description

This project is a REST API built in PHP for a smart appliance company called **Kahuna**.
It allows users to create accounts, log in, register purchased products, and view their registered products.

Admins can also add new products to the system.

The API was tested using **Postman** and the project runs using **Docker with MariaDB**.

---

## Technologies Used

* PHP
* MariaDB
* Docker
* Postman
* GitHub

---

## Setup Instructions

1. Clone the repository

```
git clone https://github.com/abigailanastasi97/abigail.git
```

2. Navigate into the project folder

```
cd kahuna-project
```

3. Start the Docker containers

```
docker compose up
```

4. The API will run at

```
http://localhost:8000
```

---

## API Endpoints

### Public

* POST `/api/index.php/createAccount`
* POST `/api/index.php/login`

### Client (Authenticated)

* POST `/api/index.php/registerProduct`
* GET `/api/index.php/viewProducts`
* GET `/api/index.php/viewProduct`
* POST `/api/index.php/logout`

### Admin

* POST `/api/index.php/addProduct`

---

## Testing

The API can be tested using the provided **Postman collection**.

Import:

* `kahuna_collection.json`
* `kahuna_environment.json`

Then run the requests in Postman.

---

## Author

Final Project – MySuccess Website Developer Course
