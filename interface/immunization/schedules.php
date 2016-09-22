<?php
function validationImmunizationCode ($post) {
    foreach ($post as &$item) {
        $item = strip_tags($item);
    }

    return $post;
}
include_once("../globals.php");
include_once("$srcdir/registry.inc");
include_once("$srcdir/sql.inc");
$dsn = "mysql:host=". $sqlconf['host'] .";dbname=" . $sqlconf['dbase'] . ";charset=utf8";
$opt = array(
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
);
$pdo = new PDO($dsn, $sqlconf['login'], $sqlconf['pass'], $opt);

if(isset($_POST['immunization_schedule'])) {
    $post = validationImmunizationCode($_POST['immunization_schedule']);

    if($post['id']) {
        $stmt = $pdo->prepare("UPDATE immunizations_schedules set description = :description,
            age = :age,
            age_max = :age_max,
            frequency = :frequency
            where id=:id");
        $stmt->bindParam(':description', $post['description']);
        $stmt->bindParam(':age', $post['age']);
        $stmt->bindParam(':age_max', $post['age_max']);
        $stmt->bindParam(':frequency', $post['frequency']);
        $stmt->bindParam(':id', $post['id']);

        $update = $stmt->execute();
        if($update) {
            $successMessage = 'Schedule updated success!';
        } else {
            $errorMessage = 'SQL error';
        }

    } else {
        $stmt = $pdo->prepare("INSERT INTO immunizations_schedules (description, age, age_max, frequency) VALUES (?,?,?,?)");
        $insert = $stmt->execute(array($post['description'], $post['age'], $post['age_max'], $post['frequency']));
        if($insert) {
            $successMessage = 'Schedule added success!';
        } else {
            $errorMessage = 'SQL error';
        }
    }
}

if(isset($errorMessage)) {
    require_once 'temp/schedule_form.php';
    exit;
}
if(isset($_GET['action'])) {
    switch($_GET['action']) {
        case 'add':
            $action = "add";
            require_once 'temp/schedule_form.php';
            exit;
            break;
        case 'edit':
            if(isset($_GET['id'])) {
                $query = $pdo->prepare("SELECT * FROM immunizations_schedules WHERE id = :id");
                $query->execute(array('id' => $_GET['id']));
                $result = $query->fetch();
            }
            $action = "edit";
            require_once 'temp/schedule_form.php';
            exit;
            break;
        case 'del':
            if(isset($_GET['id'])) {
                $query = $pdo->prepare("DELETE FROM immunizations_schedules WHERE id = :id");
                $del = $query->execute(array('id' => $_GET['id']));
                if($del) {
                    $successMessage = 'Schedule deleted success!';
                } else {
                    $errorMessage = 'SQL error';
                }
            }
            break;
    }
}

$results = $pdo->query("SELECT * FROM immunizations_schedules");
$rows = $results->fetchAll();
require_once 'temp/view_table_schedule.php';

?>
