# DateRange-Picker-IPA
This repository contains my IPA project (Individuelle Praktische Arbeit), which serves as the final practical examination before receiving the EFZ.

## Project description
The goal of this project is to extend an existing Laravel-based dashboard by integrating a DateRange-Picker that allows users to freely select a start and end date.

### After choosing a date-range:
- The backend generates time-series data.
- The frontend dynamically aggregates the data depending on the selected range.
- The results are visualized in existing Chart.js components.

### Focus of this project:
- REST API design
- Robust input validation
- Deterministic time handling (UTC-based)
- Dynamic frontend aggregation
- Appealing and userfriendly frontend date-range picker component.
- Tests for quality assurance

## Tech-stack
### Backend:
- Laravel
- PHP
- Carbon (date handling)
- Tests for quality assurance

### Frontend:
- Vue.js
- Chart.js
- vue2-datepicker and styling
- Axios

### Testing:
- PHPUnit feature and integration tests
- Node-based tests for frontend aggregation-logic


# Project setup instructions
## Requirements
### Required software:
- PHP
- Composer
- Node.js
- npm
- XAMPP (Apache & MySQL)
- Git

### Clone the repository
```Bash
git clone https://github.com/Schoolprojects-devin-mugglin/DateRange-Picker-IPA
cd DateRange-Picker-IPA
```

## Backend setup
### Step 1
```Bash
composer install
```

### Step 2
```Bash
cp .env.example .env
```

### Step 3
```Bash
php artisan key:generate
```

### Step 4
```Bash
php artisan migrate
```

## Frontend setup
### Step 1
```Bash
npm install
```

### Step 2
```Bash
npm run watch
```

## Start the Application
### Step 1
```Bash
php artisan serve
```

### Step 2
```Code
http://127.0.0.1:8000
```

## Running tests
### Backend tests
```Bash
php artisan test
```

### Frontend aggregation test
```Bash
node tests/js/timeseriesAggregation.test.js
```