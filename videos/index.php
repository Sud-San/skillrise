<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$video_id = isset($_GET['video_id']) ? intval($_GET['video_id']) : 0;
$lesson_id = isset($_GET['lesson_id']) ? intval($_GET['lesson_id']) : 0;

if ($course_id <= 0) {
    header("Location: ../courses.php");
    exit;
}


// Fetch Course Details
$courseStmt = $conn->prepare("SELECT course_title FROM course_tbl WHERE course_id = ?");
$courseStmt->bind_param("i", $course_id);
$courseStmt->execute();
$courseRes = $courseStmt->get_result()->fetch_assoc();

if (!$courseRes) {
    header("Location: ../courses.php");
    exit;
}

// Fetch All Lessons for this course LEFT JOINED with videos_tbl
$videosStmt = $conn->prepare("
    SELECT l.*, v.video_id, v.video_url, v.video_status, v.uploaded_at 
    FROM lessons_tbl l
    LEFT JOIN videos_tbl v ON l.lesson_id = v.lesson_id 
    WHERE l.course_id = ? 
    ORDER BY l.lesson_order ASC
");
$videosStmt->bind_param("i", $course_id);
$videosStmt->execute();
$videosResult = $videosStmt->get_result();
$videos = [];
while ($row = $videosResult->fetch_assoc()) {
    $videos[] = $row;
}
$lesson_count = mysqli_num_rows($videosResult);

// Determine which lesson/video to play
$active_video = null;
if ($lesson_id > 0) {
    foreach ($videos as $v) {
        if ($v['lesson_id'] == $lesson_id) {
            $active_video = $v;
            break;
        }
    }
} elseif ($video_id > 0) {
    foreach ($videos as $v) {
        if ($v['video_id'] == $video_id) {
            $active_video = $v;
            break;
        }
    }
}

if (!$active_video && !empty($videos)) {
    $active_video = $videos[0];
}

// Check if user is enrolled
$is_enrolled = false;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $checkStmt = $conn->prepare("SELECT * FROM enrollments_tbl WHERE user_id = ? AND course_id = ? AND enrollment_status = 'active'");
    $checkStmt->bind_param("ii", $user_id, $course_id);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows > 0) {
        $is_enrolled = true;
    }
}

// Access Control Logic
$can_watch = false;
if ($active_video) {
    if ($active_video['lesson_order'] == 1) {
        $can_watch = true; // First lesson is free
    } elseif ($is_enrolled) {
        $can_watch = true; // Enrolled users can watch all
    }
}

// Progress Update
if ($is_enrolled && $active_video) {
    $new_progress = round((($active_video['lesson_order'] - 1) / $lesson_count) * 100);

    if (isset($_GET['finished']) && $_GET['finished'] == 1 && $active_video['lesson_order'] == $lesson_count) {
        $new_progress = 100;
        $upd = $conn->prepare("UPDATE enrollments_tbl SET progress = 100, completed_at = NOW(), certificate_issued = '1' WHERE user_id = ? AND course_id = ?");
        $upd->bind_param("ii", $user_id, $course_id);
        $upd->execute();
    } else {
        $check = $conn->prepare("SELECT progress FROM enrollments_tbl WHERE user_id = ? AND course_id = ?");
        $check->bind_param("ii", $user_id, $course_id);
        $check->execute();
        $curr = $check->get_result()->fetch_assoc();

        if ($curr && $new_progress > $curr['progress']) {
            $upd = $conn->prepare("UPDATE enrollments_tbl SET progress = ? WHERE user_id = ? AND course_id = ?");
            $upd->bind_param("iii", $new_progress, $user_id, $course_id);
            $upd->execute();
        }
    }
}

function e($str)
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en" class="light scroll-smooth" dir="ltr">

<head>
    <meta charset="UTF-8" />
    <title><?php echo e($active_video['lesson_title'] ?? 'Watch Course'); ?> | <?php echo $company_name; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="icon" sizes="180x180" href="../codez3.png" />
    <link href="../assets/libs/remixicon/fonts/remixicon.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#054b40',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            overflow-x: hidden;
        }

        .video-wrapper {
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            aspect-ratio: 16/9;
            width: 100%;
            overflow: hidden;
        }

        /* Force Plyr to take full height/width of wrapper */
        .video-wrapper .plyr {
            height: 100% !important;
            width: 100% !important;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .video-wrapper .plyr__video-wrapper {
            height: 100% !important;
            background: #000;
        }

        .video-wrapper video {
            height: 100% !important;
            width: 100% !important;
            object-fit: contain;
            /* Prevents stretching while filling space */
        }

        @media (min-width: 1024px) {
            body {
                overflow: hidden;
            }

            .video-wrapper {
                height: 100%;
                aspect-ratio: auto;
            }

            .sidebar-container {
                height: calc(100vh - 64px);
                width: 380px;
                flex-shrink: 0;
            }
        }

        .sidebar-container {
            overflow-y: auto;
            background: #f8fafc;
            border-left: 1px solid #e2e8f0;
        }

        .lesson-item {
            border-left: 4px solid transparent;
            transition: all 0.2s;
        }

        .lesson-item.active {
            background-color: #f1f5f9;
            border-left-color: #054b40;
        }

        .plyr--full-ui.plyr--video .plyr__control--hover {
            background: #054b40 !important;
        }

        .plyr__control--overlaid {
            background: #054b40 !important;
        }
    </style>
</head>

<body class="bg-white">
    <!-- Clean Header -->
    <header class="h-16 border-b bg-white flex items-center px-4 sticky top-0 z-50">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center gap-3 min-w-0">
                <a href="../user-mycourses.php"
                    class="flex-shrink-0 text-gray-400 hover:text-primary transition-colors">
                    <i class="ri-arrow-left-line text-2xl"></i>
                </a>
                <h1 class="text-base md:text-lg font-bold text-gray-800 truncate">
                    <?php echo e($courseRes['course_title']); ?>
                </h1>
            </div>
            <div class="flex-shrink-0 ml-4 flex items-center gap-3">
                <span
                    class="text-[10px] md:text-xs font-bold text-primary bg-primary/10 px-3 py-1 rounded-full uppercase">
                    <?php echo count($videos); ?> Lessons
                </span>
            </div>
        </div>
    </header>

    <main class="flex flex-col lg:flex-row h-auto lg:h-[calc(100vh-64px)]">
        <!-- Player Space (Left) -->
        <div class="flex-1 flex flex-col bg-black lg:overflow-hidden relative">
            <div class="video-wrapper lg:flex-1">
                <?php if (!$active_video || empty($active_video['video_url'])): ?>
                    <div class="text-center text-white p-10">
                        <i class="ri-video-off-line text-6xl opacity-20"></i>
                        <p class="mt-4 text-gray-400 font-bold">Video not available</p>
                    </div>
                <?php elseif ($can_watch): ?>
                    <div class="w-full h-full flex items-center justify-center">
                        <video id="player" playsinline controls class="w-full h-full">
                            <source src="<?php echo $active_video['video_url']; ?>" type="video/mp4">
                        </video>
                    </div>
                <?php else: ?>
                    <div
                        class="flex flex-col items-center justify-center p-12 text-center bg-gray-950 w-full h-full aspect-video">
                        <div class="max-w-sm">
                            <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-6">
                                <i class="ri-lock-2-fill text-3xl text-primary"></i>
                            </div>
                            <h2 class="text-xl font-black text-white mb-2">Private Lesson</h2>
                            <p class="text-gray-400 mb-6 text-sm">Please enroll in this course to gain immediate access to
                                this video lesson.</p>
                            <a href="../course-detail.php?id=<?php echo $course_id; ?>"
                                class="bg-primary text-white px-8 py-3 rounded-xl font-bold text-xs hover:scale-105 transition-all inline-block shadow-lg shadow-primary/20">
                                Enroll Now
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Meta Section -->
            <?php if ($active_video): ?>
                <div class="bg-white p-6 border-t border-gray-100 hidden lg:block">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-black text-primary uppercase tracking-widest mb-1 block">Selected
                                Lesson</span>
                            <h2 class="text-xl font-black text-gray-900 leading-tight">
                                <?php echo e($active_video['lesson_title']); ?>
                            </h2>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-px h-8 bg-gray-200"></div>
                            <div class="pl-3">
                                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">Uploaded On</p>
                                <p class="text-xs font-bold text-gray-700">
                                    <?php

                                    echo date("d M, Y", strtotime($active_video['uploaded_at'])); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar (Right) -->
        <aside class="sidebar-container overflow-x-hidden">
            <div class="p-5 border-b bg-white/80 backdrop-blur-md sticky top-0 z-10">
                <h3 class="font-black text-gray-900 tracking-tight flex items-center gap-2">
                    <i class="ri-play-list-2-line text-primary"></i>
                    Playlist
                </h3>
            </div>

            <div class="flex flex-col">
                <?php foreach ($videos as $v):
                    $is_active = ($active_video && $v['lesson_id'] == $active_video['lesson_id']);
                    $is_v_locked = (!$is_enrolled && $v['lesson_order'] > 1);
                    ?>
                    <a href="?course_id=<?php echo $course_id; ?>&lesson_id=<?php echo $v['lesson_id']; ?>"
                        class="flex items-center gap-4 p-5 border-b hover:bg-white transition-all lesson-item <?php echo $is_active ? 'active' : ''; ?> <?php echo $is_v_locked ? 'opacity-40 pointer-events-none' : ''; ?>">
                        <div
                            class="flex-shrink-0 w-9 h-9 rounded-xl flex items-center justify-center font-black text-xs <?php echo $is_active ? 'bg-primary text-white' : 'bg-gray-100 text-gray-400'; ?>">
                            <?php if ($is_active): ?>
                                <i class="ri-play-fill text-base"></i>
                            <?php else: ?>
                                <?php echo $v['lesson_order']; ?>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4
                                class="text-sm font-bold truncate <?php echo $is_active ? 'text-primary' : 'text-gray-700'; ?>">
                                <?php echo e($v['lesson_title']); ?>
                            </h4>
                            <div class="flex items-center gap-2 mt-0.5">
                                <?php if ($v['lesson_order'] == 1 && !$is_enrolled): ?>
                                    <span
                                        class="text-[8px] bg-emerald-100 text-emerald-600 px-1.5 py-0.5 rounded-md font-black uppercase tracking-tighter">Preview</span>
                                <?php elseif ($is_v_locked): ?>
                                    <span
                                        class="text-[8px] text-gray-400 font-bold flex items-center gap-0.5 uppercase tracking-tighter"><i
                                            class="ri-lock-line"></i> Locked</span>
                                <?php else: ?>
                                    <span class="text-[8px] text-primary/40 font-bold uppercase tracking-tighter">Lesson</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </aside>
    </main>

    <script src="https://cdn.plyr.io/3.7.8/plyr.js"></script>
    <script>
        const player = new Plyr('#player', {
            controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'settings', 'fullscreen'],
            settings: ['speed'],
            speed: { selected: 1, options: [0.5, 0.75, 1, 1.25, 1.5, 2] }
        });

        // Smart Resume
        <?php if ($active_video && !empty($active_video['video_url'])): ?>
            const videoId = 'sr_v_<?php echo $active_video['video_id'] ?? 'l_' . $active_video['lesson_id']; ?>';
            player.on('ready', () => {
                const time = localStorage.getItem(videoId);
                if (time) player.currentTime = parseFloat(time);
            });
            player.on('timeupdate', () => {
                localStorage.setItem(videoId, player.currentTime);
            });

            // Auto Advance
            player.on('ended', () => {
                <?php
                $next_v = null;
                $current_idx = -1;
                foreach ($videos as $i => $v) {
                    if ($v['lesson_id'] == $active_video['lesson_id']) {
                        $current_idx = $i;
                        if (isset($videos[$i + 1])) {
                            $next_v = $videos[$i + 1];
                        }
                        break;
                    }
                }
                ?>
                <?php if ($next_v): ?>
                    // Go to next lesson
                    window.location.href = "?course_id=<?php echo $course_id; ?>&lesson_id=<?php echo $next_v['lesson_id']; ?>";
                <?php elseif ($active_video['lesson_order'] == $lesson_count): ?>
                    // Final lesson completed - reload with finished flag to trigger 100% update in PHP
                    window.location.href = window.location.href + "&finished=1";
                <?php endif; ?>
            });
        <?php endif; ?>
    </script>
</body>

</html>