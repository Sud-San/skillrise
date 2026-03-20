<?php
include 'connection.php';

$search = $_POST['query'] ?? '';

if ($search === '') {
    $query = "SELECT * FROM category_tbl";
} else {
    $query = "SELECT * FROM category_tbl WHERE category_name LIKE '%$search%'";
}

$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {

        // Get language name and convert to lowercase for file naming
        $lang = strtolower($row['category_name']);

        // Set dynamic paths and content based on language
        $imagePath = "assets/images/{$lang}.png";
        $desc = "";

        switch ($lang) {
            case "html":
                $desc = "Understand HTML structure, tags, and web page design essentials.";
                break;
            case "css":
                $desc = "Learn how to style web pages with CSS — colors, layouts, and animations.";
                break;
            case "javascript":
                $desc = "Master client-side scripting and DOM manipulation with JavaScript.";
                break;
            case "python":
                $desc = "Explore Python basics, syntax, and problem-solving techniques.";
                break;
            default:
                $desc = "Explore resources and notes for {$row['category_name']}.";
                break;
        }

        echo '
        <div class="group p-6 bg-white dark:bg-gray-900 shadow-lg rounded-xl border border-gray-100 dark:border-gray-800 transition-all duration-500 hover:scale-[1.03] hover:shadow-xl">
            <div class="text-center">
                <img src="' . $imagePath . '" alt="' . $row['category_name'] . '" class="w-20 h-20 mx-auto mb-4 rounded-full shadow-md">
                <h5 class="text-xl font-semibold text-primary mb-2">' . $row['category_name'] . ' Notes</h5>
                <p class="text-gray-500 dark:text-gray-400 mb-3">' . $desc . '</p>
                <a href="javascript:void(0)" class="view-more-link" onclick="toggleCheats(\'cheat' . $row['category_id'] . '\')">View More →</a>
                <div id="cheat' . $row['category_id'] . '" class="cheatsheet-buttons transition-all duration-500 ease-in-out hidden">
                    <a href="uploads/' . $lang . '_cheatsheet1.pdf" download>Cheatsheet 1</a>
                    <a href="uploads/' . $lang . '_cheatsheet2.pdf" download>Cheatsheet 2</a>
                    <a href="uploads/' . $lang . '_cheatsheet3.pdf" download>Cheatsheet 3</a>
                </div>
            </div>
        </div>';
    }
} else {
    echo '<p class="text-center text-gray-500 mt-6 col-span-3">No notes found 😔</p>';
}
?>