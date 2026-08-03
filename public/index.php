<?php
//christian angelo a. alba
//V-2
// ============================================
// LOAD SLIM FRAMEWORK
// ============================================
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response as SlimResponse;

// ============================================
// CREATE SLIM APPLICATION
// ============================================
$app = AppFactory::create();

// SET BASE PATH - IMPORTANT FOR XAMPP!
$app->setBasePath('/filipino-cookbook-api/public');

// Add middleware
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

// ============================================
// DATABASE CONFIGURATION
// ============================================
$db_config = [
    'host' => 'localhost',
    'dbname' => 'filipino_cookbook_api',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

// ============================================
// DATABASE CONNECTION FUNCTION
// ============================================
function getDBConnection() {
    global $db_config;
    try {
        $dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
        $pdo = new PDO($dsn, $db_config['username'], $db_config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception("Database connection failed: " . $e->getMessage());
    }
}

// ============================================
// API TOKEN
// ============================================
define('API_TOKEN', 'dmmmsu-cookbook-token-2026');

// ============================================
// TOKEN VALIDATION FUNCTION
// ============================================
function validateToken(Request $request): bool {
    $authHeader = $request->getHeaderLine('Authorization');
    if (empty($authHeader)) {
        return false;
    }
    if (preg_match('/^Bearer\s+(.+)$/', $authHeader, $matches)) {
        return $matches[1] === API_TOKEN;
    }
    return false;
}

// ============================================
// JSON RESPONSE HELPER
// ============================================
function jsonResponse(Response $response, $data, int $statusCode = 200): Response {
    $payload = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json')->withStatus($statusCode);
}

// ============================================
// GET INGREDIENTS HELPER
// ============================================
function getFoodIngredients(PDO $pdo, int $food_id): array {
    $stmt = $pdo->prepare("
        SELECT i.ingredient_name 
        FROM ingredients i
        JOIN food_ingredients fi ON i.ingredient_id = fi.ingredient_id
        WHERE fi.food_id = :food_id
        ORDER BY i.ingredient_name
    ");
    $stmt->execute(['food_id' => $food_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// ============================================
// ROUTE 1: WELCOME (PUBLIC - NO TOKEN)
// ============================================
$app->get('/', function (Request $request, Response $response) {
    $data = [
        'message' => 'Welcome to the Secured Filipino Cookbook API',
        'note' => 'Use a valid Bearer token to access /api endpoints.'
    ];
    return jsonResponse($response, $data);
});

// ============================================
// SECURED ROUTES (TOKEN REQUIRED)
// ============================================
$app->group('/api', function ($group) {
    
    // GET ALL FOODS
    $group->get('/foods', function (Request $request, Response $response) {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->query("
                SELECT f.food_id, f.food_name, c.category_name, o.origin_name, f.instructions
                FROM foods f
                JOIN categories c ON f.category_id = c.category_id
                JOIN origins o ON f.origin_id = o.origin_id
                ORDER BY f.food_name
            ");
            $foods = $stmt->fetchAll();
            foreach ($foods as &$food) {
                $food['ingredients'] = getFoodIngredients($pdo, $food['food_id']);
            }
            return jsonResponse($response, $foods);
        } catch (Exception $e) {
            return jsonResponse($response, ['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    });

    // GET FOOD BY ID
    $group->get('/foods/{id:[0-9]+}', function (Request $request, Response $response, array $args) {
        try {
            $pdo = getDBConnection();
            $food_id = (int)$args['id'];
            $stmt = $pdo->prepare("
                SELECT f.food_id, f.food_name, c.category_name, o.origin_name, f.instructions
                FROM foods f
                JOIN categories c ON f.category_id = c.category_id
                JOIN origins o ON f.origin_id = o.origin_id
                WHERE f.food_id = :food_id
            ");
            $stmt->execute(['food_id' => $food_id]);
            $food = $stmt->fetch();
            if (!$food) {
                return jsonResponse($response, ['status' => 'error', 'message' => 'Food not found'], 404);
            }
            $food['ingredients'] = getFoodIngredients($pdo, $food_id);
            return jsonResponse($response, $food);
        } catch (Exception $e) {
            return jsonResponse($response, ['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    });

    // SEARCH FOOD BY NAME
    $group->get('/foods/search/{name}', function (Request $request, Response $response, array $args) {
        try {
            $pdo = getDBConnection();
            $search_term = '%' . $args['name'] . '%';
            $stmt = $pdo->prepare("
                SELECT f.food_id, f.food_name, c.category_name, o.origin_name, f.instructions
                FROM foods f
                JOIN categories c ON f.category_id = c.category_id
                JOIN origins o ON f.origin_id = o.origin_id
                WHERE f.food_name LIKE :search_term
                ORDER BY f.food_name
            ");
            $stmt->execute(['search_term' => $search_term]);
            $foods = $stmt->fetchAll();
            if (empty($foods)) {
                return jsonResponse($response, ['status' => 'error', 'message' => 'No foods found'], 404);
            }
            foreach ($foods as &$food) {
                $food['ingredients'] = getFoodIngredients($pdo, $food['food_id']);
            }
            return jsonResponse($response, $foods);
        } catch (Exception $e) {
            return jsonResponse($response, ['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    });

    // ============================================
    // NEW: GLOBAL SEARCH - SEARCH EVERYTHING
    // ============================================
    $group->get('/search/{term}', function (Request $request, Response $response, array $args) {
        try {
            $pdo = getDBConnection();
            $search_term = '%' . $args['term'] . '%';
            
            $results = [
                'foods' => [],
                'ingredients' => [],
                'categories' => [],
                'origins' => []
            ];
            
            // Search foods
            $stmt = $pdo->prepare("
                SELECT f.food_id, f.food_name, c.category_name, o.origin_name, f.instructions
                FROM foods f
                JOIN categories c ON f.category_id = c.category_id
                JOIN origins o ON f.origin_id = o.origin_id
                WHERE f.food_name LIKE :search_term 
                   OR f.instructions LIKE :search_term
                   OR c.category_name LIKE :search_term
                   OR o.origin_name LIKE :search_term
                ORDER BY f.food_name
            ");
            $stmt->execute(['search_term' => $search_term]);
            $foods = $stmt->fetchAll();
            
            foreach ($foods as &$food) {
                $food['ingredients'] = getFoodIngredients($pdo, $food['food_id']);
            }
            $results['foods'] = $foods;
            
            // Search ingredients
            $stmt = $pdo->prepare("
                SELECT i.*, COUNT(fi.food_id) as food_count
                FROM ingredients i
                LEFT JOIN food_ingredients fi ON i.ingredient_id = fi.ingredient_id
                WHERE i.ingredient_name LIKE :search_term
                GROUP BY i.ingredient_id
                ORDER BY i.ingredient_name
            ");
            $stmt->execute(['search_term' => $search_term]);
            $results['ingredients'] = $stmt->fetchAll();
            
            // Search categories
            $stmt = $pdo->prepare("
                SELECT c.*, COUNT(f.food_id) as food_count
                FROM categories c
                LEFT JOIN foods f ON c.category_id = f.category_id
                WHERE c.category_name LIKE :search_term
                GROUP BY c.category_id
                ORDER BY c.category_name
            ");
            $stmt->execute(['search_term' => $search_term]);
            $results['categories'] = $stmt->fetchAll();
            
            // Search origins
            $stmt = $pdo->prepare("
                SELECT o.*, COUNT(f.food_id) as food_count
                FROM origins o
                LEFT JOIN foods f ON o.origin_id = f.origin_id
                WHERE o.origin_name LIKE :search_term
                GROUP BY o.origin_id
                ORDER BY o.origin_name
            ");
            $stmt->execute(['search_term' => $search_term]);
            $results['origins'] = $stmt->fetchAll();
            
            // Check if any results found
            $hasResults = !empty($results['foods']) || 
                         !empty($results['ingredients']) || 
                         !empty($results['categories']) || 
                         !empty($results['origins']);
            
            if (!$hasResults) {
                return jsonResponse($response, [
                    'status' => 'error',
                    'message' => 'No results found for: ' . $args['term']
                ], 404);
            }
            
            return jsonResponse($response, [
                'status' => 'success',
                'search_term' => $args['term'],
                'total_results' => [
                    'foods' => count($results['foods']),
                    'ingredients' => count($results['ingredients']),
                    'categories' => count($results['categories']),
                    'origins' => count($results['origins'])
                ],
                'data' => $results
            ]);
            
        } catch (Exception $e) {
            return jsonResponse($response, [
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    });

    // ============================================
    // NEW: SEARCH FOODS BY INGREDIENT
    // ============================================
    $group->get('/foods/by-ingredient/{ingredient_name}', function (Request $request, Response $response, array $args) {
        try {
            $pdo = getDBConnection();
            $search_term = '%' . $args['ingredient_name'] . '%';
            
            $stmt = $pdo->prepare("
                SELECT DISTINCT f.food_id, f.food_name, c.category_name, o.origin_name, f.instructions
                FROM foods f
                JOIN categories c ON f.category_id = c.category_id
                JOIN origins o ON f.origin_id = o.origin_id
                JOIN food_ingredients fi ON f.food_id = fi.food_id
                JOIN ingredients i ON fi.ingredient_id = i.ingredient_id
                WHERE i.ingredient_name LIKE :search_term
                ORDER BY f.food_name
            ");
            $stmt->execute(['search_term' => $search_term]);
            $foods = $stmt->fetchAll();
            
            if (empty($foods)) {
                return jsonResponse($response, [
                    'status' => 'error',
                    'message' => 'No foods found with ingredient: ' . $args['ingredient_name']
                ], 404);
            }
            
            foreach ($foods as &$food) {
                $food['ingredients'] = getFoodIngredients($pdo, $food['food_id']);
            }
            
            return jsonResponse($response, [
                'status' => 'success',
                'search_ingredient' => $args['ingredient_name'],
                'count' => count($foods),
                'data' => $foods
            ]);
            
        } catch (Exception $e) {
            return jsonResponse($response, [
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    });

    // ============================================
    // NEW: SEARCH FOODS BY CATEGORY
    // ============================================
    $group->get('/foods/by-category/{category_name}', function (Request $request, Response $response, array $args) {
        try {
            $pdo = getDBConnection();
            $search_term = '%' . $args['category_name'] . '%';
            
            $stmt = $pdo->prepare("
                SELECT f.food_id, f.food_name, c.category_name, o.origin_name, f.instructions
                FROM foods f
                JOIN categories c ON f.category_id = c.category_id
                JOIN origins o ON f.origin_id = o.origin_id
                WHERE c.category_name LIKE :search_term
                ORDER BY f.food_name
            ");
            $stmt->execute(['search_term' => $search_term]);
            $foods = $stmt->fetchAll();
            
            if (empty($foods)) {
                return jsonResponse($response, [
                    'status' => 'error',
                    'message' => 'No foods found in category: ' . $args['category_name']
                ], 404);
            }
            
            foreach ($foods as &$food) {
                $food['ingredients'] = getFoodIngredients($pdo, $food['food_id']);
            }
            
            return jsonResponse($response, [
                'status' => 'success',
                'category' => $args['category_name'],
                'count' => count($foods),
                'data' => $foods
            ]);
            
        } catch (Exception $e) {
            return jsonResponse($response, [
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    });

    // ============================================
    // NEW: SEARCH FOODS BY ORIGIN
    // ============================================
    $group->get('/foods/by-origin/{origin_name}', function (Request $request, Response $response, array $args) {
        try {
            $pdo = getDBConnection();
            $search_term = '%' . $args['origin_name'] . '%';
            
            $stmt = $pdo->prepare("
                SELECT f.food_id, f.food_name, c.category_name, o.origin_name, f.instructions
                FROM foods f
                JOIN categories c ON f.category_id = c.category_id
                JOIN origins o ON f.origin_id = o.origin_id
                WHERE o.origin_name LIKE :search_term
                ORDER BY f.food_name
            ");
            $stmt->execute(['search_term' => $search_term]);
            $foods = $stmt->fetchAll();
            
            if (empty($foods)) {
                return jsonResponse($response, [
                    'status' => 'error',
                    'message' => 'No foods found from origin: ' . $args['origin_name']
                ], 404);
            }
            
            foreach ($foods as &$food) {
                $food['ingredients'] = getFoodIngredients($pdo, $food['food_id']);
            }
            
            return jsonResponse($response, [
                'status' => 'success',
                'origin' => $args['origin_name'],
                'count' => count($foods),
                'data' => $foods
            ]);
            
        } catch (Exception $e) {
            return jsonResponse($response, [
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    });

    // ============================================
    // NEW: ADVANCED SEARCH WITH MULTIPLE PARAMETERS
    // ============================================
    $group->get('/foods/advanced-search', function (Request $request, Response $response) {
        try {
            $pdo = getDBConnection();
            $params = $request->getQueryParams();
            
            $query = "
                SELECT DISTINCT f.food_id, f.food_name, c.category_name, o.origin_name, f.instructions
                FROM foods f
                JOIN categories c ON f.category_id = c.category_id
                JOIN origins o ON f.origin_id = o.origin_id
                LEFT JOIN food_ingredients fi ON f.food_id = fi.food_id
                LEFT JOIN ingredients i ON fi.ingredient_id = i.ingredient_id
                WHERE 1=1
            ";
            
            $bindings = [];
            
            if (!empty($params['food_name'])) {
                $query .= " AND f.food_name LIKE :food_name";
                $bindings['food_name'] = '%' . $params['food_name'] . '%';
            }
            
            if (!empty($params['category'])) {
                $query .= " AND c.category_name LIKE :category";
                $bindings['category'] = '%' . $params['category'] . '%';
            }
            
            if (!empty($params['origin'])) {
                $query .= " AND o.origin_name LIKE :origin";
                $bindings['origin'] = '%' . $params['origin'] . '%';
            }
            
            if (!empty($params['ingredient'])) {
                $query .= " AND i.ingredient_name LIKE :ingredient";
                $bindings['ingredient'] = '%' . $params['ingredient'] . '%';
            }
            
            $query .= " ORDER BY f.food_name";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute($bindings);
            $foods = $stmt->fetchAll();
            
            if (empty($foods)) {
                return jsonResponse($response, [
                    'status' => 'error',
                    'message' => 'No foods found matching your criteria'
                ], 404);
            }
            
            foreach ($foods as &$food) {
                $food['ingredients'] = getFoodIngredients($pdo, $food['food_id']);
            }
            
            return jsonResponse($response, [
                'status' => 'success',
                'count' => count($foods),
                'search_params' => $params,
                'data' => $foods
            ]);
            
        } catch (Exception $e) {
            return jsonResponse($response, [
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    });

    // GET CATEGORIES
    $group->get('/categories', function (Request $request, Response $response) {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->query("SELECT * FROM categories ORDER BY category_name");
            $categories = $stmt->fetchAll();
            return jsonResponse($response, ['status' => 'success', 'data' => $categories]);
        } catch (Exception $e) {
            return jsonResponse($response, ['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    });

    // GET INGREDIENTS
    $group->get('/ingredients', function (Request $request, Response $response) {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->query("SELECT * FROM ingredients ORDER BY ingredient_name");
            $ingredients = $stmt->fetchAll();
            return jsonResponse($response, ['status' => 'success', 'data' => $ingredients]);
        } catch (Exception $e) {
            return jsonResponse($response, ['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    });

    // ADD NEW FOOD (POST)
    $group->post('/foods', function (Request $request, Response $response) {
        try {
            $pdo = getDBConnection();
            $body = $request->getParsedBody();
            
            if (!$body || !isset($body['food_name']) || !isset($body['category_id']) || 
                !isset($body['origin_id']) || !isset($body['instructions']) || !isset($body['ingredient_ids'])) {
                return jsonResponse($response, ['status' => 'error', 'message' => 'Missing required fields'], 400);
            }
            
            // Check for duplicate food name
            $stmt = $pdo->prepare("SELECT food_id FROM foods WHERE LOWER(food_name) = LOWER(:food_name)");
            $stmt->execute(['food_name' => $body['food_name']]);
            if ($stmt->fetch()) {
                return jsonResponse($response, [
                    'status' => 'error',
                    'message' => 'Food already exists. Duplicate entries are not allowed.'
                ], 409);
            }
            
            $stmt = $pdo->query("SELECT MAX(food_id) as max_id FROM foods");
            $result = $stmt->fetch();
            $next_id = ($result['max_id'] ?? 0) + 1;
            
            $stmt = $pdo->prepare("INSERT INTO foods (food_id, food_name, category_id, origin_id, instructions) VALUES (:food_id, :food_name, :category_id, :origin_id, :instructions)");
            $stmt->execute([
                'food_id' => $next_id,
                'food_name' => $body['food_name'],
                'category_id' => $body['category_id'],
                'origin_id' => $body['origin_id'],
                'instructions' => $body['instructions']
            ]);
            
            $stmt = $pdo->prepare("INSERT INTO food_ingredients (food_id, ingredient_id) VALUES (:food_id, :ingredient_id)");
            foreach ($body['ingredient_ids'] as $ingredient_id) {
                $stmt->execute(['food_id' => $next_id, 'ingredient_id' => $ingredient_id]);
            }
            
            return jsonResponse($response, ['status' => 'success', 'message' => 'Food added successfully.'], 201);
        } catch (Exception $e) {
            return jsonResponse($response, ['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    });

    // DELETE FOOD BY ID
    $group->delete('/foods/{id:[0-9]+}', function (Request $request, Response $response, array $args) {
        try {
            $pdo = getDBConnection();
            $food_id = (int)$args['id'];
            
            // Check if food exists first
            $stmt = $pdo->prepare("SELECT food_id FROM foods WHERE food_id = :food_id");
            $stmt->execute(['food_id' => $food_id]);
            $food = $stmt->fetch();
            
            if (!$food) {
                return jsonResponse($response, [
                    'status' => 'error',
                    'message' => 'Food not found'
                ], 404);
            }
            
            // Delete food (cascade will also delete from food_ingredients)
            $stmt = $pdo->prepare("DELETE FROM foods WHERE food_id = :food_id");
            $stmt->execute(['food_id' => $food_id]);
            
            return jsonResponse($response, [
                'status' => 'success',
                'message' => 'Food deleted successfully.'
            ]);
            
        } catch (Exception $e) {
            return jsonResponse($response, [
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    });

})->add(function ($request, $handler) {
    // Token validation middleware
    if (!validateToken($request)) {
        $response = new SlimResponse();
        return jsonResponse($response, [
            'status' => 'error',
            'message' => 'Unauthorized access. Valid API token is required.'
        ], 401);
    }
    return $handler->handle($request);
});

// ============================================
// RUN THE APP
// ============================================
$app->run();