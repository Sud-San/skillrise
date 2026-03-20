<?php
session_start();
include 'connection.php';

$isLoggedIn = isset($_SESSION['user_id']) ? true : false;
?>

<!DOCTYPE html>
<html lang="en" class="light scroll-smooth" dir="ltr">
<head>
    <?php include 'headtag.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
	.view-more-link {
		color: #16a34a; /* green */
		font-weight: 600;
		text-decoration: none;
		display: inline-block;
		margin-top: 5px;
	}
	.view-more-link:hover {
		text-decoration: underline;
	}
	.cheatsheet-buttons {
		display: none;
		margin-top: 12px;
	}
	.cheatsheet-buttons a {
		display: block;
		background: linear-gradient(to right, #16a34a, #22c55e);
		color: white;
		padding: 10px 14px;
		border-radius: 8px;
		margin-bottom: 10px;
		text-align: center;
		text-decoration: none;
		font-weight: 500;
		transition: background 0.3s ease;
	}
	.cheatsheet-buttons a:hover {
		background: linear-gradient(to right, #15803d, #16a34a);
	}
	</style>

</head>

<body class="text-gray-900 dark:text-white dark:bg-gray-900">
    <?php include 'header.php'; ?>
	<!-- Start Hero -->
    <section class="relative table bg-primary w-full py-24">
        <div class="absolute inset-0 bg-[url('../assets/images/bg/box.php')] bg-no-repeat bg-center bg-cover"></div>
        <div class="container relative">
            <div class="grid grid-cols-1 text-center mt-10">
                <h3 class="text-4xl md:leading-normal leading-normal font-semibold text-white">NOTES</h3>

                <ul class="tracking-[0.5px] inline-block mt-2">
                    <li class="inline-block font-medium uppercase duration-500 text-xs ease-in-out text-white/70 dark:text-white/50 hover:text-white dark:hover:text-white"><a href="index.php"><?php echo $company_name; ?></a></li>
                    <li class="inline-block text-white/70 dark:text-white/50 mx-0.5 text-sm ltr:rotate-0 rtl:rotate-180"><i class="ri-arrow-right-s-line"></i></li>
                    <li class="inline-block font-medium uppercase duration-500 text-xs ease-in-out text-white dark:text-white" aria-current="page">Notes</li>
                </ul>
            </div><!--end grid-->
        </div><!--end container-->
    </section><!--end section-->
    <!-- End Hero -->
	

    <!-- Notes Section -->
    <section class="relative lg:py-24 py-16">
        <div class="container relative">
            <div class="grid grid-cols-1 pb-6 text-center">
                <h4 class="mb-4 text-3xl font-semibold">📚 Programming Notes</h4>
                <p class="text-gray-400 max-w-xl mx-auto">
                    Access well-structured notes and helpful cheat sheets for quick revision!
                </p>
            </div>
			
			
			<!-- 🔍 Search Bar -->
			<div class="text-center mb-8">
				<input type="text" id="search" 
					   placeholder="Search notes (e.g. Python, PHP, HTML)..." 
					   class="border border-gray-300 rounded-lg px-4 py-2 w-150 focus:ring focus:ring-primary/30">
			</div>
			
			<br>
			<script>
        $(document).ready(function(){
            function load_data(query = '') {
                $.ajax({
                    url: "search_notes.php",
                    method: "POST",
                    data: { query: query },
                    success: function(data){
                        $('#resultContainer').html(data);
                    }
                });
            }

            // Load all languages by default
            load_data();

            // Live search
            $('#searchBox').keyup(function(){
                var search = $(this).val();
                load_data(search);
            });
        });
    </script>
            <div id="notesContainer" class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-6 mt-6">

               <!-- HTML -->
				<div class="group p-6 bg-white dark:bg-gray-900 shadow-lg rounded-xl border border-gray-100 dark:border-gray-800 transition-all duration-500 hover:scale-[1.03] hover:shadow-xl">
					<div class="text-center">
						<img src="assets/images/html.png" alt="HTML" class="w-20 h-20 mx-auto mb-4 rounded-full shadow-md">
						<h5 class="text-xl font-semibold text-primary mb-2">HTML Notes</h5>
						<p class="text-gray-500 dark:text-gray-400 mb-3">Understand HTML structure, tags, and web page design essentials.</p>
						<a href="javascript:void(0)" class="view-more-link" onclick="toggleCheats('cheat6')">View More →</a>
						<div id="cheat6" class="cheatsheet-buttons transition-all duration-500 ease-in-out">
							<a href="uploads/html1.pdf" class="downloadBtn" download>Cheatsheet 1</a>
							<a href="uploads/html2.pdf" class="downloadBtn" download>Cheatsheet 2</a>
							<a href="uploads/html3.pdf" class="downloadBtn" download>Cheatsheet 3</a>
						</div>
					</div>
				</div>

				<!-- Java -->
				<div class="group p-6 bg-white dark:bg-gray-900 shadow-lg rounded-xl border border-gray-100 dark:border-gray-800 transition-all duration-500 hover:scale-[1.03] hover:shadow-xl">
					<div class="text-center">
						<img src="assets/images/java.png" alt="Java" class="w-20 h-20 mx-auto mb-4 rounded-full shadow-md">
						<h5 class="text-xl font-semibold text-primary mb-2">JAVA Notes</h5>
						<p class="text-gray-500 dark:text-gray-400 mb-3">Master object-oriented programming, core concepts, and examples.</p>
						<a href="javascript:void(0)" class="view-more-link" onclick="toggleCheats('cheat7')">View More →</a>
						<div id="cheat7" class="cheatsheet-buttons transition-all duration-500 ease-in-out">
							<a href="uploads/java1.pdf" class="downloadBtn" download>Cheatsheet 1</a>
							<a href="uploads/java2.pdf" class="downloadBtn" download>Cheatsheet 2</a>
							<a href="uploads/java3.pdf" class="downloadBtn" download>Cheatsheet 3</a>
						</div>
					</div>
				</div>


                <!-- Python -->
				<div class="group p-6 bg-white dark:bg-gray-900 shadow-lg rounded-xl border border-gray-100 dark:border-gray-800 transition-all duration-500 hover:scale-[1.03] hover:shadow-xl">
					<div class="text-center">
						<img src="assets/images/python.png" alt="Python" class="w-20 h-20 mx-auto mb-4 rounded-full shadow-md">
						<h5 class="text-xl font-semibold text-primary mb-2">PYTHON Notes</h5>
						<p class="text-gray-500 dark:text-gray-400 mb-3">Learn Python syntax, libraries, and real-world examples.</p>
						<a href="javascript:void(0)" class="view-more-link" onclick="toggleCheats('cheat3')">View More →</a>
						<div id="cheat3" class="cheatsheet-buttons transition-all duration-500 ease-in-out">
							<a href="uploads/python1.pdf" class="downloadBtn" download>Cheatsheet 1</a>
							<a href="uploads/python2.pdf" class="downloadBtn" download>Cheatsheet 2</a>
							<a href="uploads/python3.pdf" class="downloadBtn" download>Cheatsheet 3</a>
						</div>
					</div>
				</div>
				
				<!-- PHP -->
				<div class="group p-6 bg-white dark:bg-gray-900 shadow-lg rounded-xl border border-gray-100 dark:border-gray-800 transition-all duration-500 hover:scale-[1.03] hover:shadow-xl">
					<div class="text-center">
						<img src="assets/images/php.png" alt="PHP" class="w-20 h-20 mx-auto mb-4 rounded-full shadow-md">
						<h5 class="text-xl font-semibold text-primary mb-2">PHP Notes</h5>
						<p class="text-gray-500 dark:text-gray-400 mb-3">Learn to build dynamic and interactive web applications using PHP.</p>
						<a href="javascript:void(0)" class="view-more-link" onclick="toggleCheats('cheat4')">View More →</a>
						<div id="cheat4" class="cheatsheet-buttons transition-all duration-500 ease-in-out">
							<a href="uploads/php1.pdf" class="downloadBtn" download>Cheatsheet 1</a>
							<a href="uploads/php2.pdf" class="downloadBtn" download>Cheatsheet 2</a>
							<a href="uploads/php3.pdf" class="downloadBtn" download>Cheatsheet 3</a>
						</div>
					</div>
				</div>
				
				<!-- UNIX -->
				<div class="group p-6 bg-white dark:bg-gray-900 shadow-lg rounded-xl border border-gray-100 dark:border-gray-800 transition-all duration-500 hover:scale-[1.03] hover:shadow-xl">
					<div class="text-center">
						<img src="assets/images/unix.png" alt="UNIX" class="w-20 h-20 mx-auto mb-4 rounded-full shadow-md">
						<h5 class="text-xl font-semibold text-primary mb-2">UNIX Notes</h5>
						<p class="text-gray-500 dark:text-gray-400 mb-3">Explore commands, shell scripting, and system administration concepts.</p>
						<a href="javascript:void(0)" class="view-more-link" onclick="toggleCheats('cheat5')">View More →</a>
						<div id="cheat5" class="cheatsheet-buttons transition-all duration-500 ease-in-out">
							<a href="uploads/unix1.pdf" class="downloadBtn" download>Cheatsheet 1</a>
							<a href="uploads/unix2.pdf" class="downloadBtn" download>Cheatsheet 2</a>
							<a href="uploads/unix3.pdf" class="downloadBtn" download>Cheatsheet 3</a>
						</div>
					</div>
				</div>

            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script src="assets/libs/tiny-slider/min/tiny-slider.js"></script>
    <script src="assets/js/plugins.init.js"></script>
    <script src="assets/js/app.js"></script>

	
	<script>
	function toggleCheats(id) {
		const div = document.getElementById(id);
		if (div.style.display === "block") {
			$(div).slideUp(300);
		} else {
			$(div).slideDown(300);
		}
	}
	</script>

<script>
$(document).ready(function() {
    $('#search').on('keyup', function() {
        var query = $(this).val();

        $.ajax({
            url: 'search_notes.php',
            type: 'POST',
            data: { query: query },
            success: function(data) {
                $('#notesContainer').html(data);
            }
        });
    });
});
</script>

<script>
let isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;

document.querySelectorAll('.downloadBtn').forEach(button => {
    button.addEventListener('click', function(e) {
        if (!isLoggedIn) {
            e.preventDefault();
			<?php $_SESSION['prelogin_redirect'] = $_SERVER['REQUEST_URI']; ?>
            window.location.href = "login.php";
        }
    });
});
</script>


</body>
</html>
