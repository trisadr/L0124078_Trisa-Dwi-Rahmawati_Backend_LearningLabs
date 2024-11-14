<?php
    // Menampilkan semua error
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Koneksi ke database
    $errors = "";
    $db = mysqli_connect('localhost', 'root', '', 'todo_app');

    // Tambah Tugas Baru
    if (isset($_POST['submit'])) {
        $title = $_POST['task'];
        $description = $_POST['description'];
        if (empty($title)) {
            $errors = "Judul tugas harus diisi!";
        } else {
            mysqli_query($db, "INSERT INTO tasks (title, description, status) VALUES ('$title', '$description', 'pending')");
            header('location: index.php');
        }
    }

    // Hapus Tugas
    if (isset($_GET['del_task'])) {
        $id = $_GET['del_task'];
        mysqli_query($db, "DELETE FROM tasks WHERE id = $id");
        header('location: index.php');
    }

    // Update Tugas
    if (isset($_POST['edit_task'])) {
        $id = $_POST['task_id'];
        $title = $_POST['task'];
        $description = $_POST['description'];
        if (!empty($title)) {
            mysqli_query($db, "UPDATE tasks SET title='$title', description='$description' WHERE id=$id");
            header('location: index.php');
        } else {
            $errors = "Judul tugas harus diisi!";
        }
    }

    // Mengubah Status Tugas
    if (isset($_GET['toggle_status'])) {
        $id = $_GET['toggle_status'];
        $current_status = $_GET['current_status'];
        $new_status = ($current_status == 'pending') ? 'completed' : 'pending';
        mysqli_query($db, "UPDATE tasks SET status='$new_status' WHERE id=$id");
        header('location: index.php');
    }

    // Ambil Semua Tugas dari Database
    $tasks = mysqli_query($db, "SELECT * FROM tasks");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    
</head>
<body>
    <div class="heading">
        <h2>To-Do List</h2>
    </div>

    <form method="POST" action="index.php" id="taskForm">
        <?php if(isset($errors) && !empty($errors)) { ?>
            <p class="error"><?php echo $errors; ?></p>
        <?php } ?>
        <input type="text" name="task" id="taskInput" class="task_input" placeholder="Tambahkan tugas baru">
        <textarea name="description" id="descInput" class="desc_input" placeholder="Deskripsi tugas"></textarea>
        <button type="submit" class="add_btn" name="submit">Tambah tugas</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Judul</th>
                <th>Deskripsi</th>
                <th>Status</th>
                <th>Hapus/ Ganti Judul</th>
            </tr>
        </thead>

        <tbody>
        <?php $i = 1; while ($row = mysqli_fetch_array($tasks)) { ?>
            <tr>
                <td><?php echo $i; ?></td>
                <td class="title <?php echo ($row['status'] == 'completed') ? 'completed' : ''; ?>">
                    <?php echo $row['title']; ?>
                </td>
                <td class="description"><?php echo $row['description']; ?></td>
                <td>
                    <button class="toggle_status_btn" 
                            onclick="window.location.href='index.php?toggle_status=<?php echo $row['id']; ?>&current_status=<?php echo $row['status']; ?>'">
                        <?php echo ucfirst($row['status']); ?>
                    </button>
                </td>
                <td class="actions">
                    <a href="index.php?del_task=<?php echo $row['id']; ?>" class="delete">✖</a>
                    <button class="edit_btn" 
                            data-id="<?php echo $row['id']; ?>" 
                            data-task="<?php echo $row['title']; ?>" 
                            data-description="<?php echo $row['description']; ?>">
                        ✎
                    </button>
                </td>
            </tr>
        <?php $i++; } ?>
        </tbody>
    </table>

    <!-- Modal for editing task -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <form method="POST" action="index.php">
                <input type="hidden" name="task_id" id="task_id">
                <input type="text" name="task" id="edit_task_input" class="task_input">
                <textarea name="description" id="edit_desc_input" class="desc_input"></textarea>
                <button type="submit" name="edit_task" class="add_btn">Save</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('editModal');
            const closeModal = document.querySelector('.close');
            const editButtons = document.querySelectorAll('.edit_btn');
            const taskInput = document.getElementById('edit_task_input');
            const descInput = document.getElementById('edit_desc_input');
            const taskIdInput = document.getElementById('task_id');

            // Buka Modal Saat Klik Tombol Edit
            editButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const taskId = button.getAttribute('data-id');
                    const taskText = button.getAttribute('data-task');
                    const descText = button.getAttribute('data-description');

                    taskInput.value = taskText;
                    descInput.value = descText;
                    taskIdInput.value = taskId;
                    modal.style.display = 'flex';
                });
            });

            // Tutup Modal
            closeModal.addEventListener('click', () => {
                modal.style.display = 'none';
            });

            // Tutup Modal Saat Klik di Luar Modal
            window.addEventListener('click', (event) => {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>