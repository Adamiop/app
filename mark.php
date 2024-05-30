<?php
require_once('process/dbh.php');

// Initialize variables
$pname = $duedate = $subdate = $firstName = $lastName = $mark = $points = $base = $bonus = $total = '';

// Get project and employee details
$id = isset($_GET['id']) ? $_GET['id'] : '';
$pid = isset($_GET['pid']) ? $_GET['pid'] : '';

if (!empty($id) && !empty($pid)) {
    // Fetch the project details
    $sql = "SELECT pid, project.eid, project.pname, project.duedate, project.subdate, project.mark, 
            rank.points, employee.firstName, employee.lastName, salary.base, salary.bonus, salary.total 
            FROM `project`
            JOIN `rank` ON project.eid = rank.eid 
            JOIN `employee` ON project.eid = employee.id 
            JOIN `salary` ON project.eid = salary.id 
            WHERE project.eid = ? AND project.pid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $id, $pid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        while ($res = $result->fetch_assoc()) {
            $pname = $res['pname'];
            $duedate = $res['duedate'];
            $subdate = $res['subdate'];
            $firstName = $res['firstName'];
            $lastName = $res['lastName'];
            $mark = $res['mark'];
            $points = $res['points'];
            $base = $res['base'];
            $bonus = $res['bonus'];
            $total = $res['total'];
        }
    }
    $stmt->close();
} else {
    echo "Error: Missing project or employee ID.";
    exit;
}

// Update project mark and related details
if (isset($_POST['update'])) {
    $eid = mysqli_real_escape_string($conn, $_POST['eid']);
    $pid = mysqli_real_escape_string($conn, $_POST['pid']);
    $mark = mysqli_real_escape_string($conn, $_POST['mark']);
    $points = mysqli_real_escape_string($conn, $_POST['points']);
    $base = mysqli_real_escape_string($conn, $_POST['base']);
    $bonus = mysqli_real_escape_string($conn, $_POST['bonus']);
    $total = mysqli_real_escape_string($conn, $_POST['total']);

    $upPoint = $points + $mark;
    $upBonus = $bonus + $mark;
    $upSalary = $base + ($upBonus * $base) / 100;

    // Update project mark
    $stmt = $conn->prepare("UPDATE `project` SET `mark` = ? WHERE eid = ? AND pid = ?");
    $stmt->bind_param('iii', $mark, $eid, $pid);
    $stmt->execute();

    // Update rank points
    $stmt = $conn->prepare("UPDATE `rank` SET `points` = ? WHERE eid = ?");
    $stmt->bind_param('ii', $upPoint, $eid);
    $stmt->execute();

    // Update salary
    $stmt = $conn->prepare("UPDATE `salary` SET `bonus` = ?, `total` = ? WHERE id = ?");
    $stmt->bind_param('dii', $upBonus, $upSalary, $eid);
    $stmt->execute();

    $stmt->close();

    echo ("<SCRIPT LANGUAGE='JavaScript'>
            window.location.href='assignproject.php';
           </SCRIPT>");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Project Mark | XYZ Corporation</title>
    <!-- Icons font CSS-->
    <link href="vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">
    <link href="vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <!-- Font special for pages-->
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,100i,300,300i,400,400i,500,500i,700,700i,900,900i" rel="stylesheet">
    <!-- Vendor CSS-->
    <link href="vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="vendor/datepicker/daterangepicker.css" rel="stylesheet" media="all">
    <!-- Main CSS-->
    <link href="css/main.css" rel="stylesheet" media="all">
</head>
<body>
    <header>
        <nav>
            <h1>XYZ Corp.</h1>
            <ul id="navli">
                <li><a class="homeblack" href="aloginwel.php">HOME</a></li>
                <li><a class="homeblack" href="addemp.php">Add Employee</a></li>
                <li><a class="homeblack" href="viewemp.php">View Employee</a></li>
                <li><a class="homeblack" href="assign.php">Assign Project</a></li>
                <li><a class="homered" href="assignproject.php">Project Status</a></li>
                <li><a class="homeblack" href="salaryemp.php">Salary Table</a></li>
                <li><a class="homeblack" href="empleave.php">Employee Leave</a></li>
                <li><a class="homeblack" href="alogin.html">Log Out</a></li>
            </ul>
        </nav>
    </header>

    <div class="divider"></div>

    <div class="page-wrapper bg-blue p-t-100 p-b-100 font-robo">
        <div class="wrapper wrapper--w680">
            <div class="card card-1">
                <div class="card-heading"></div>
                <div class="card-body">
                    <h2 class="title">Project Mark</h2>
                    <form id="registration" action="mark.php?id=<?php echo $id; ?>&pid=<?php echo $pid; ?>" method="POST">
                        <div class="input-group">
                            <p>Project Name</p>
                            <input class="input--style-1" type="text" name="pname" value="<?php echo $pname; ?>" readonly>
                        </div>
                        <div class="input-group">
                            <p>Employee Name</p>
                            <input class="input--style-1" type="text" name="firstName" value="<?php echo $firstName . ' ' . $lastName; ?>" readonly>
                        </div>
                        <div class="row row-space">
                            <div class="col-2">
                                <div class="input-group">
                                    <p>Due Date</p>
                                    <input class="input--style-1" type="text" name="duedate" value="<?php echo $duedate; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="input-group">
                                    <p>Submission Date</p>
                                    <input class="input--style-1" type="text" name="subdate" value="<?php echo $subdate; ?>" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="input-group">
                            <p>Assign Mark</p>
                            <input class="input--style-1" type="text" name="mark" value="<?php echo $mark; ?>">
                        </div>
                        <input type="hidden" name="eid" value="<?php echo $id; ?>" required>
                        <input type="hidden" name="pid" value="<?php echo $pid; ?>" required>
                        <input type="hidden" name="points" value="<?php echo $points; ?>" required>
                        <input type="hidden" name="base" value="<?php echo $base; ?>" required>
                        <input type="hidden" name="bonus" value="<?php echo $bonus; ?>" required>
                        <input type="hidden" name="total" value="<?php echo $total; ?>" required>
                        <div class="p-t-20">
                            <button class="btn btn--radius btn--green" type="submit" name="update">Assign Mark</button>
                        </div>
                    </form>
                    <br>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
