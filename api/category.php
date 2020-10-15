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
			if (!empty($_GET["id"])) {
				$id = intval($_GET["id"]);
				update($id);
			}else {
				store();
			}
			break;

		case 'DELETE':
			$id = $_GET['id'];
			destroy($id);
			break;
		
		default:
			header("HTTP/1.0 405 Method Not Allowed");
			$response = array(
					'status' => "0",
					'status_message' => "Method Not Allowed"
				);
			echo json_encode($response);
			break;
	}

	function index() {

		global $pdo;
		$sql = "SELECT * FROM categories";
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
					"name" => $row["category_name"]
				);
				array_push($categories_arr["data"], $category);
			}
		}

			http_response_code(200);
			echo json_encode($categories_arr);
	}

	function show($id) {
		global $pdo;
		$sql = "SELECT * FROM categories where id=:id";
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
					"name" => $row["category_name"]
				);
				array_push($categories_arr["data"], $category);
			}
		}
		http_response_code(200);
		echo json_encode($categories_arr);
	}

	function store() {

		global $pdo;
		$name = $_POST['category_name'];
		$image = $_FILES['category_image'];
		/*var_dump($image);die();*/

		$source_dir = "../image/";
		$file_path = $source_dir.$image['name'];
		$image_file = "/image/".$image['name'];

		move_uploaded_file($image['tmp_name'], $file_path);

			if (!empty($name) && !empty($file_path)) {
			$sql = "SELECT * FROM categories where category_name=:name";
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(":name", $name);
			$stmt->execute();
			if ($stmt->rowCount()) {
				$response = array(
					'status' => "0",
					'status_message' => "That name is already added in database"
				);
			}else {
				$sql = "INSERT INTO categories(category_name,category_image) VALUES (:name,:image)";
				$stmt = $pdo->prepare($sql);
				$stmt->bindParam(":name", $name);
				$stmt->bindParam(":image", $image_file);
				$stmt->execute();
				if ($stmt->rowCount()) {
					$response = array(
					'status' => "1",
					'status_message' => "Category is added successfully"
					);
				}else {
					$response = array(
					'status' => "0",
					'status_message' => "Category cann't added to database"
					);
				}
			}
		}else {
			$response = array(
				'status' => "0",
				'status_message' => "Category is required"
				);
		}

		echo json_encode($response);
	}

	function update($id) {

		global $pdo;
		$name = $_POST['category_name'];
		if (!empty($name)) {
			
			$sql = "UPDATE categories SET category_name=:name WHERE id=:id";
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(":name", $name);
			$stmt->bindParam(":id", $id);
			$stmt->execute();

			if ($stmt->rowCount()) {
				$response = array(
					'status' => "1",
					'status_message' => "Existing Category is updated sucessfully"
				);
			}else {
				$response = array(
					'status' => "0",
					'status_message' => "Existing Category cannot updated"
				);
			}

		}else {
			$response = array(
					'status' => "0",
					'status_message' => "Category Name is required"
				);
		}
		echo json_encode($response);
	}

	function destroy($id) {
		global$pdo;

		$sql = "DELETE FROM categories where id=:id";
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(":id", $id);
		$stmt->execute();

		if ($stmt->rowCount()) {
				$response = array(
					'status' => "1",
					'status_message' => "Existing Category is deleted sucessfully"
				);
			}else {
				$response = array(
					'status' => "0",
					'status_message' => "Existing Category cannot delete"
				);
		}
		echo json_encode($response);
	}
?>