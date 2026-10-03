<?php
/**
 * Student Boarding House Management System
 * Main Entry Point
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

define('BASE_PATH', __DIR__);

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/Database.php';
require_once BASE_PATH . '/app/Model.php';
require_once BASE_PATH . '/app/Controller.php';
require_once BASE_PATH . '/app/Router.php';
require_once BASE_PATH . '/app/Helpers/helpers.php';
require_once BASE_PATH . '/app/SchemaGuard.php';

// Load Services
require_once BASE_PATH . '/app/Services/RecaptchaService.php';

// Load Controllers
require_once BASE_PATH . '/app/Controllers/HomeController.php';
require_once BASE_PATH . '/app/Controllers/AuthController.php';
require_once BASE_PATH . '/app/Controllers/StudentController.php';
require_once BASE_PATH . '/app/Controllers/ManagerController.php';
require_once BASE_PATH . '/app/Controllers/AdminController.php';
require_once BASE_PATH . '/app/Controllers/ArchiveController.php';

// Load Routes
require_once BASE_PATH . '/routes/web.php';

// Handle Request
SchemaGuard::ensure();
Router::dispatch();
