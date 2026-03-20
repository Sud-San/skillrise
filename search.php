<?php
// include 'connection.php';

// if (isset($_POST['query'])) {
//     $query = mysqli_real_escape_string($conn, $_POST['query']);

//     $langQuery = "SELECT l.lang_id, l.name,
//                     (SELECT COUNT(*) FROM course_tbl c WHERE c.rlanguage_id = l.lang_id) AS course_count
//                   FROM languages_tbl l
//                   WHERE l.name LIKE '%$query%'";

//     $langResult = mysqli_query($conn, $langQuery);

//     $langMeta = [
//         'Python' => [
//             'desc' => 'Python is simple yet powerful. Perfect for AI, ML, and web development.',
//             'img'  => 'python.png'
//         ],
//         'PHP' => [
//             'desc' => 'PHP powers the web. Learn to create interactive and dynamic websites.',
//             'img'  => 'php.png'
//         ],
//         'Java' => [
//             'desc' => 'Java is robust and scalable. Ideal for mobile and enterprise software.',
//             'img'  => 'java.png'
//         ],
//         'HTML' => [
//             'desc' => 'HTML is the foundation of web design. Start structuring your site now.',
//             'img'  => 'html.png'
//         ],
//         'UNIX' => [
//             'desc' => 'Master Linux and shell scripting to automate tasks and manage systems.',
//             'img'  => 'unix.png'
//         ]
//     ];

//     if (mysqli_num_rows($langResult) > 0) {
//         while ($row = mysqli_fetch_assoc($langResult)) {
//             $name = $row['name'];
//             $meta = $langMeta[$name] ?? ['desc' => "Explore courses in $name.", 'img' => 'default.png'];

//             echo "
//             <div class='group bg-white dark:bg-gray-900 rounded-lg shadow shadow-gray-200 dark:shadow-gray-800 p-6 flex flex-col h-[420px] transition hover:shadow-lg duration-300'>
//                 <div class='flex-grow text-center flex flex-col justify-start'>
//                     <img src='assets/images/{$meta['img']}' alt='{$name}' class='w-16 h-16 mx-auto mb-4'>
//                     <h3 class='text-xl font-semibold text-primary'>{$name}</h3>
//                     <p class='text-gray-600 dark:text-gray-300 text-sm mt-2 mb-4 line-clamp-2 leading-snug'>
//                         {$meta['desc']}
//                     </p>
//                     <p class='text-sm text-gray-400 mb-1'>{$row['course_count']} Courses Available</p>
//                 </div>
//                 <div class='mt-4 text-center'>
//                     <a href='courses-by-language.php?lang=" . urlencode($name) . "' class='inline-block text-sm text-primary hover:underline'>
//                         View Courses <i class='ri-arrow-right-line align-middle'></i>
//                     </a>
//                 </div>
//             </div>";
//         }
//     } else {
//         echo "<p class='col-span-full text-center text-gray-500'>No results found.</p>";
//     }
// }
?>