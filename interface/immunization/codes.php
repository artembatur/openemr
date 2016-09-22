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

if(isset($_POST['immunization_code'])) {
    $post = validationImmunizationCode($_POST['immunization_code']);

    if($post['id']) {
        $stmt = $pdo->prepare("UPDATE immunizations_schedules_codes set description = :description,
            manufacturer = :manufacturer,
            cvx_code = :cvx_code,
            proc_codes = :proc_codes,
            justify_codes = :justify_codes,
            default_site = :default_site,
            drug_route = :drug_route,
            description = :description
            where id=:id");
        $stmt->bindParam(':description', $post['description']);
        $stmt->bindParam(':manufacturer', $post['manufacturer']);
        $stmt->bindParam(':cvx_code', $post['cvx_code']);
        $stmt->bindParam(':proc_codes', $post['proc_codes']);
        $stmt->bindParam(':justify_codes', $post['justify_codes']);
        $stmt->bindParam(':default_site', $post['default_site']);
        $stmt->bindParam(':comments', $post['comments']);
        $stmt->bindParam(':drug_route', $post['drug_route']);
        $stmt->bindParam(':id', $post['id']);

        $update = $stmt->execute();
        if($update) {
            $successMessage = 'Code updated success!';
        } else {
            $errorMessage = 'SQL error';
        }

    } else {
        $stmt = $pdo->prepare("INSERT INTO immunizations_schedules_codes (description, manufacturer, cvx_code, proc_codes, justify_codes, default_site, comments, drug_route) VALUES (?,?,?,?,?,?,?,?)");
        $insert = $stmt->execute(array($post['description'], $post['manufacturer'], $post['cvx_code'], $post['proc_codes'], $post['justify_codes'], $post['default_site'], $post['comments'], $post['drug_route']));
        if($insert) {
            $successMessage = 'Code added success!';
        } else {
            $errorMessage = 'SQL error';
        }
    }
}

if(isset($errorMessage)) {
    require_once 'temp/code_form.php';
    exit;
}
if(isset($_GET['action'])) {
    switch($_GET['action']) {
        case 'add':
            $action = "add";
            require_once 'temp/code_form.php';
            exit;
            break;
        case 'edit':
            if(isset($_GET['id'])) {
                $query = $pdo->prepare("SELECT * FROM immunizations_schedules_codes WHERE id = :id");
                $query->execute(array('id' => $_GET['id']));
                $result = $query->fetch();
            }
            $action = "edit";
            require_once 'temp/code_form.php';
            exit;
            break;
        case 'del':
            if(isset($_GET['id'])) {
                $query = $pdo->prepare("DELETE FROM immunizations_schedules_codes WHERE id = :id");
                $del = $query->execute(array('id' => $_GET['id']));
                if($del) {
                    $successMessage = 'Code deleted success!';
                } else {
                    $errorMessage = 'SQL error';
                }
            }
            break;
    }
}

$results = $pdo->query("SELECT * FROM immunizations_schedules_codes");
$rows = $results->fetchAll();
require_once 'temp/view_table.php';

?>
