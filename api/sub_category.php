<?php 
	
	header("Access-Control-Allow-Origin: *");
	header("Content-Type: application/json");

	require '../db_connect.php';

	$request_method = $_SERVER["REQUEST_METHOD"];

	switch ($request_method) {
		case 'GET':
			if (!empty($_GET["id"])) {
				$id = intval($_GET["id"]);
				show($id);
			}else {
				index();
			}
			break;

		case 'POST':
			if (!empty($_POST["id"])) {
				$id = intval($_POST["id"]);
				update($id);
			}else {
				store();
			}
			break;
		
		default:
			# code...
			break;
	}

	function index() {

		global $pdo;
		$sql = "SELECT *, categories.category_name FROM sub_categories INNER JOIN categories ON categories.id=sub_categories.category_id";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$rows = $stmt->fetchAll();

		$categories_arr = array();

		if ($stmt->rowCount() <= 0) {
			$categories_arr["status"] = 0;
			$categories_arr["status_message"] = "Something went wrong";
		}else {

			$categories_arr["status"] = 1;
			$categories_arr["status_message"] = "200 OK";

			$categories_arr["data"] = array();

			foreach ($rows as $row) {
				$category = array(
					"id" => $row["id"],
					"name" => $row["sub_category_name"],
					"category_name" => $row["category_name"]
				);
				array_push($categories_arr["data"], $category);
			}
		}

			http_response_code(200);
			echo json_encode($categories_arr);
	}

	function show($id) {
		global $pdo;
		$sql = "SELECT *, categories.category_name FROM sub_categories INNER JOIN categories ON categories.id=sub_categories.category_id where sub_categories.id=:id";
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(":id", $id);
		$stmt->execute();

		$rows = $stmt->fetchAll();

		$categories_arr = array();

		if ($stmt->rowCount() <=0 ) {
			$categories_arr["status"] = "0";
			$categories_arr["status_message"] = "Something went worng";
		}else {
			$categories_arr["status"] = "1";
			$categories_arr["status_message"] = "200 OK";
			$categories_arr["data"] = array();

			foreach ($rows as $row) {
				$category = array(
					"id" => $row["id"],
					"name" => $row["sub_category_name"],
					"category_name" => $row["category_name"]
				);
				array_push($categories_arr["data"], $category);
			}
		}
		http_response_code(200);
		echo json_encode($categories_arr);
	}

	function store() {

		global $pdo;
		$name = $_POST['sub_category_name'];
		$category_id = $_POST['category_id'];

		if (!empty($name) && !empty($category_id)) {
			$sql = "SELECT * FROM sub_categories where sub_category_name=:name";
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(":name", $name);
			$stmt->execute();
			if ($stmt->rowCount()) {
				$response = array(
					'status' => "0",
					'status_message' => "That name is already added in database"
				);
			}else {
				$sql = "INSERT INTO sub_categories(sub_category_name,category_id) VALUES (:name,:category_id)";
				$stmt = $pdo->prepare($sql);
				$stmt->bindParam(":name", $name);
				$stmt->bindParam(":category_id", $category_id);
				$stmt->execute();
				if ($stmt->rowCount()) {
					$response = array(
					'status' => "1",
					'status_message' => "Sub Category is added successfully"
					);
				}else {
					$response = array(
					'status' => "0",
					'status_message' => "Sub Category cann't added to database"
					);
				}
			}
		}else {
			$response = array(
				'status' => "0",
				'status_message' => "Sub Category || Category Id is required"
				);
		}

		echo json_encode($response);
	}

?>