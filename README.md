# Uber Clone Backend API

## Overview

This project is the backend service for a real-time ride-hailing platform inspired by Uber. It is built with Laravel and provides authentication, ride management, driver tracking, payment processing, and real-time communication between riders and drivers.

The system focuses on delivering a responsive ride-booking experience by combining REST APIs, real-time location updates, and payment processing through PayPal.

## Key Features

### Real-Time Driver Tracking

One of the core challenges of ride-hailing applications is keeping driver locations synchronized across multiple clients.

This project implements real-time driver tracking using WebSockets through Laravel Reverb. Driver devices continuously transmit GPS coordinates to the backend, allowing rider applications to receive live location updates without requiring page refreshes.

Benefits:

* Live driver movement on rider maps
* Reduced polling overhead
* Improved user experience
* Near real-time ride monitoring

### Ride Lifecycle Management

The backend manages the entire ride workflow:

1. Rider creates ride request
2. Available drivers receive request
3. Driver reviews route and ride details
4. Driver accepts ride
5. Rider receives driver information
6. Driver location updates in real time
7. Driver completes ride
8. Ride status finalized
9. Driver commission deducted

### Authentication System

Separate authentication flows are implemented for:

* Riders
* Drivers

Features include:

* Registration
* Login
* Protected endpoints
* Driver profile management

### PayPal Payment Integration

The platform integrates PayPal for secure payment processing.

Workflow:

1. Create PayPal order
2. Redirect user through payment process
3. Capture payment upon approval
4. Confirm successful transaction
5. Continue ride workflow

### Driver Wallet & Commission Logic

Each driver account maintains an internal wallet balance.

Business rules:

* Drivers start with a balance of 20
* Commission is deducted after ride completion
* Drivers with balances below zero cannot accept additional rides
* Drivers receive clear feedback when account balance prevents ride acceptance

This simulates the commission-based model used by many ride-hailing platforms.

---

## Tech Stack

* Laravel
* Laravel Reverb
* WebSockets
* PayPal REST APIs
* MySQL
* RESTful API Architecture

---

## Available API Endpoints

### Authentication

| Method | Endpoint        |
| ------ | --------------- |
| POST   | /driverregister |
| POST   | /driverlogin    |
| POST   | /userregister   |
| POST   | /userlogin      |

### Ride Management

| Method | Endpoint         |
| ------ | ---------------- |
| POST   | /createride      |
| GET    | /getriderequests |
| POST   | /driveraccept    |
| POST   | /drivercomplete  |

### Driver Management

| Method | Endpoint              |
| ------ | --------------------- |
| GET    | /getdriverdetails     |
| POST   | /updatedriverinfo     |
| POST   | /updatedriverlocation |

### Rider Management

| Method | Endpoint         |
| ------ | ---------------- |
| GET    | /getriderdetails |

### Payments

| Method | Endpoint            |
| ------ | ------------------- |
| POST   | /createpaypalorder  |
| POST   | /capturepaypalorder |

---

## Running the Project

### Install Dependencies

```bash
composer install
```

### Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Update database and PayPal credentials in `.env`.

### Run Migrations

```bash
php artisan migrate
```

### Start Development Server

```bash
concurrently "php artisan serve" "php artisan reverb:start"
```

This starts:

* Laravel HTTP API server
* Laravel Reverb WebSocket server

---

## Architecture Highlights

### Real-Time Communication Layer

Driver GPS coordinates are continuously pushed to the backend through WebSocket connections.

Instead of repeatedly polling the server for location updates, rider clients subscribe to live updates, resulting in:

* Lower latency
* Reduced server load
* Better scalability
* Smoother map interactions

### State Management

The backend maintains ride status transitions to ensure:

* Drivers cannot accept multiple rides simultaneously
* Riders cannot create duplicate active ride requests
* Ride completion is handled consistently

---

## Future Improvements

* Driver availability zones
* Surge pricing
* Ride history analytics
* Push notifications
* Driver ratings and reviews
* Multi-payment provider support
* Background location optimization

---

## What This Project Demonstrates

* REST API development with Laravel
* Real-time systems using WebSockets
* Payment gateway integration
* Geolocation services
* Ride dispatch workflows
* Authentication and authorization
* Business rule implementation
* Full-stack system design
