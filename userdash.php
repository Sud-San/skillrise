<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Student Dashboard</title>

    <style>
        body {
            background: linear-gradient(180deg, #f4eaff, #f7f1ff);
        }
    </style>
</head>
<body class="min-h-screen p-6">

    <div class="flex gap-6">

        <!-- SIDEBAR -->
        <aside class="w-64 bg-white rounded-3xl shadow-md p-6">

            <div class="flex items-center gap-3 mb-10">
                <div class="w-10 h-10 flex items-center justify-center bg-purple-100 text-purple-600 rounded-xl font-bold text-xl">+</div>
                <h1 class="text-xl font-semibold text-gray-800 tracking-wide">STUDENT</h1>
            </div>

            <!-- Menu -->
            <nav class="space-y-3">
                <a class="flex items-center gap-3 px-4 py-3 bg-purple-100 text-purple-700 rounded-xl font-medium">
                    <span>🏠</span> Dashboard
                </a>
                <a class="flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-xl">
                    <span>📚</span> Courses
                </a>
                <a class="flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-xl">
                    <span>👤</span> Profile
                </a>
                <a class="flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-xl">
                    <span>⚙️</span> Settings
                </a>
            </nav>

        </aside>

        <!-- MAIN CONTENT -->
        <main class="flex-1">

            <!-- HEADER -->
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-3xl font-bold text-gray-800">Dashboard</h2>

                <div class="flex items-center gap-4">
                    <input type="text" placeholder="Search courses…"
                        class="px-4 py-2 rounded-xl border border-gray-300 shadow-sm focus:ring-purple-300 w-64" />

                    <img src="/mnt/data/acc3dcfb-3fd9-4d8f-8e21-f288334afc55.png"
                         class="w-10 h-10 rounded-full object-cover shadow" />
                </div>
            </div>

            <!-- ENROLLED COURSES -->
            <h3 class="text-2xl font-semibold text-gray-800 mb-5">Enrolled Courses</h3>

            <div class="bg-white rounded-3xl shadow-md p-7 w-full max-w-3xl">

                <div class="flex items-center gap-5">
                    <!-- Course Icon -->
                    <div class="w-16 h-16 bg-gray-100 rounded-xl flex items-center justify-center text-3xl">
                        🎨
                    </div>

                    <!-- Course Title -->
                    <div>
                        <h4 class="text-xl font-bold text-gray-800">UVUX Design</h4>
                        <p class="text-gray-500 text-sm">Responsive Design + 32 Lessons</p>
                    </div>

                    <span class="ml-auto text-purple-600 font-semibold text-lg">57%</span>
                </div>

                <!-- Enrollment Date -->
                <p class="text-sm text-gray-600 mt-5">📅 Enrolled on 15th Apr 2025</p>

                <!-- Progress Bar -->
                <div class="w-full h-3 bg-gray-200 rounded-full mt-3">
                    <div class="h-full bg-purple-500 rounded-full" style="width: 57%;"></div>
                </div>

                <!-- Resume Button -->
                <button class="mt-6 w-full py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-2xl text-lg font-semibold shadow">
                    Resume Course
                </button>

            </div>

            <!-- PROFILE CARD -->
            <div class="bg-white rounded-3xl shadow-md p-6 w-full max-w-2xl mt-6 flex items-center gap-6">

                <img src="/mnt/data/acc3dcfb-3fd9-4d8f-8e21-f288334afc55.png"
                     class="w-16 h-16 rounded-full object-cover shadow" />

                <div class="flex-1">
                    <h3 class="text-xl font-bold text-gray-800">Aditi Singh</h3>
                    <p class="text-gray-500 text-sm">aditt.sngh@gmail.com</p>
                </div>

                <button class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-sm font-medium shadow">
                    View Profile
                </button>
            </div>

        </main>

    </div>

</body>
</html>
