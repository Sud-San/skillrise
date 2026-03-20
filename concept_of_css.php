<!DOCTYPE html>
<html lang="en" class="light scroll-smooth" dir="ltr">

<head>
    <?php include 'headtag.php';?>
</head>

<body class="text-gray-900 dark:text-white dark:bg-gray-900">
    <?php include 'header.php'; ?>

    <section class="relative table bg-primary w-full py-24">
        <div class="absolute inset-0 bg-[url('../assets/images/bg/box.php')] bg-no-repeat bg-center bg-cover"></div>
        <div class="container relative">
            <div class="grid grid-cols-1 text-center mt-10">
                <h3 class="text-4xl font-semibold text-white">Concepts of CSS</h3>
                <ul class="inline-block mt-2 tracking-[0.5px]">
                    <li class="inline-block text-white/70 text-xs uppercase hover:text-white"><a href="index.php"><?php echo $company_name; ?></a></li>
                    <li class="inline-block mx-0.5 text-white/70 text-sm"><i class="ri-arrow-right-s-line"></i></li>
                    <li class="inline-block text-white text-xs uppercase">CSS Concepts</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="relative lg:py-24 py-16 bg-white dark:bg-gray-900">
        <div class="container">
            <div class="text-center pb-10">
                <h2 class="text-3xl font-semibold">Introduction to CSS</h2>
                <p class="text-gray-500 dark:text-gray-300 mt-4 max-w-2xl mx-auto">Explore the building blocks of styling the web using Cascading Style Sheets.</p>
            </div>

            <div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow dark:shadow-gray-700 p-6 leading-relaxed">
                <h3 class="text-xl font-semibold text-primary mb-2">What is CSS?</h3>
                <p>CSS (Cascading Style Sheets) is used to style and layout web pages — including changes in font, color, spacing, etc.</p>

                <ul class="list-disc list-inside mt-4 space-y-1">
                    <li>HTML uses tags, CSS uses rules.</li>
                    <li>CSS is used to apply styles to HTML elements.</li>
                    <li>It allows design consistency and reusability.</li>
                </ul>

                <h3 class="text-xl font-semibold text-primary mt-6 mb-2">Why CSS?</h3>
                <ul class="list-disc list-inside space-y-1">
                    <li>Saves time through reuse</li>
                    <li>Improves site maintenance</li>
                    <li>Enhances website appearance</li>
                </ul>

                <h3 class="text-xl font-semibold text-primary mt-6 mb-2">CSS Syntax</h3>
                <p>CSS is structured using selectors and declarations:</p>
                <pre class="bg-gray-100 dark:bg-gray-700 text-sm p-4 rounded mt-2"><code>h1 {
  color: blue;
  font-size: 12px;
}</code></pre>

                <h3 class="text-xl font-semibold text-primary mt-6 mb-2">Example: Paragraph Styling</h3>
                <pre class="bg-gray-100 dark:bg-gray-700 text-sm p-4 rounded"><code>p {
  color: blue;
  text-align: center;
}</code></pre>
                <p class="mt-2">This makes all paragraphs blue and center-aligned.</p>

                <h3 class="text-xl font-semibold text-primary mt-6 mb-2">Ways to Use CSS</h3>
                <ul class="list-decimal list-inside space-y-1">
                    <li>Inline – directly in elements</li>
                    <li>Internal – within a &lt;style&gt; tag</li>
                    <li>External – using .css files</li>
                </ul>

                <h3 class="text-xl font-semibold text-primary mt-6 mb-2">What is Cascading?</h3>
                <p>“Cascading” defines how conflicts are resolved when multiple rules apply — more specific selectors or last-in-order wins.</p>

                <h3 class="text-xl font-semibold text-primary mt-6 mb-2">Advantages</h3>
                <ul class="list-disc list-inside space-y-1">
                    <li>Global styling consistency</li>
                    <li>Improved site performance</li>
                    <li>Responsive design flexibility</li>
                </ul>

                <h3 class="text-xl font-semibold text-primary mt-6 mb-2">Disadvantages</h3>
                <ul class="list-disc list-inside space-y-1">
                    <li>Browser inconsistencies</li>
                    <li>Complex to debug in large files</li>
                    <li>CSS alone can't add dynamic logic</li>
                </ul>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <a href="#" onclick="topFunction()" id="back-to-top" class="back-to-top fixed hidden text-lg rounded-lg z-10 bottom-5 end-5 size-9 text-center bg-primary/10 hover:bg-primary text-primary hover:text-white leading-9">
        <i class="ri-arrow-up-line"></i>
    </a>

    <script src="assets/js/plugins.init.js"></script>
    <script src="assets/js/app.js"></script>
</body>

</html>