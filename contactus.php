<?php
session_start();
include 'connection.php';

// Handle form submission
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $subject = mysqli_real_escape_string($conn, $_POST['subject'] ?? '');
    $message_text = mysqli_real_escape_string($conn, $_POST['message'] ?? '');

    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message_text)) {
        $message = 'All fields are required!';
        $message_type = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address!';
        $message_type = 'error';
    } else {
        // Store in database
        $sql = "INSERT INTO contact_messages (name, email, subject, message, ip_address, user_agent) 
                VALUES ('$name', '$email', '$subject', '$message_text', '{$_SERVER['REMOTE_ADDR']}', '{$_SERVER['HTTP_USER_AGENT']}')";

        if (mysqli_query($conn, $sql)) {
            $message = 'Thank you! Your message has been sent. We\'ll get back to you soon.';
            $message_type = 'success';
            $_POST = array();
        } else {
            $message = 'Sorry, there was an error sending your message. Please try again.';
            $message_type = 'error';
        }
    }
}

// Fetch FAQ from database
$faq_query = "SELECT * FROM faq WHERE status = 1 ORDER BY id ASC";
$faq_result = mysqli_query($conn, $faq_query);
$faqs = [];
if ($faq_result) {
    while ($row = mysqli_fetch_assoc($faq_result)) {
        $faqs[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="light scroll-smooth" dir="ltr">

<head>
    <?php include 'headtag.php'; ?>
    <style>
        /* Minimal custom styles */
        .map-container {
            height: 350px;
            border-radius: 8px;
            overflow: hidden;
        }

        .map-container iframe {
            width: 100%;
            height: 100%;
            border: 0;
        }

        /* Small spacing adjustment */
        .contact-section {
            margin-top: 2rem;
            margin-bottom: 3rem;
        }

        .form-section {
            margin-top: 3rem;
            margin-bottom: 3rem;
        }

        .faq-section {
            margin-top: 3rem;
            padding-top: 3rem;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>

<body class="text-gray-900 dark:text-white dark:bg-gray-900">
    <?php include 'header.php'; ?>

    <!-- Start Hero -->
    <section class="relative table bg-primary w-full py-24">
        <div class="absolute inset-0 bg-[url('../assets/images/bg/box.html')] bg-no-repeat bg-center bg-cover"></div>
        <div class="container relative">
            <div class="grid grid-cols-1 text-center mt-10">
                <h3 class="text-4xl md:leading-normal leading-normal font-semibold text-white">Contact Us</h3>

                <ul class="tracking-[0.5px] inline-block mt-2">
                    <li
                        class="inline-block font-medium uppercase duration-500 text-xs ease-in-out text-white/70 dark:text-white/50 hover:text-white dark:hover:text-white">
                        <a href="index.php"><?php echo $company_name; ?></a>
                    </li>
                    <li
                        class="inline-block text-white/70 dark:text-white/50 mx-0.5 text-sm ltr:rotate-0 rtl:rotate-180">
                        <i class="ri-arrow-right-s-line"></i>
                    </li>
                    <li class="inline-block font-medium uppercase duration-500 text-xs ease-in-out text-white dark:text-white"
                        aria-current="page">
                        Contact
                    </li>
                </ul>
            </div>
        </div>
    </section>
    <!-- End Hero -->

    <!-- Start Contact Section -->
    <section class="relative py-16">
        <div class="container">
            <!-- Map + Contact Info Section -->
            <div class="contact-section">
                <div class="grid md:grid-cols-2 grid-cols-1 gap-6">
                    <!-- Map on left -->
                    <div>
                        <div class="map-container shadow">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3719.505208571544!2d72.784786!3d21.146114!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be04d1c5e8f3e13%3A0x6d6f4d4c8e6e1e8a!2sC.%20B.%20Patel%20College%20of%20Computer%20Studies%2C%20Surat!5e0!3m2!1sen!2sin!4v1700000000002!5m2!1sen!2sin"
                                allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    </div>

                    <!-- Contact Info on right -->
                    <div class="space-y-4">
                        <div
                            class="bg-white dark:bg-gray-900 p-5 rounded-lg shadow shadow-gray-100 dark:shadow-gray-800">
                            <div class="flex items-start">
                                <div
                                    class="size-10 bg-primary/10 text-primary rounded-lg flex items-center justify-center me-3">
                                    <i class="ri-phone-line"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-1">Call Us</h4>
                                    <p class="text-gray-400">+91 81400 90385</p>
                                    <p class="text-sm text-gray-500">Mon-Fri, 9AM-6PM</p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-white dark:bg-gray-900 p-5 rounded-lg shadow shadow-gray-100 dark:shadow-gray-800">
                            <div class="flex items-start">
                                <div
                                    class="size-10 bg-primary/10 text-primary rounded-lg flex items-center justify-center me-3">
                                    <i class="ri-mail-line"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-1">Email Us</h4>
                                    <p class="text-gray-400">support@skillrise.com</p>
                                    <p class="text-sm text-gray-500">24/7 Support</p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-white dark:bg-gray-900 p-5 rounded-lg shadow shadow-gray-100 dark:shadow-gray-800">
                            <div class="flex items-start">
                                <div
                                    class="size-10 bg-primary/10 text-primary rounded-lg flex items-center justify-center me-3">
                                    <i class="ri-map-pin-line"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-1">Visit Us</h4>
                                    <p class="text-gray-400"><?php echo $company_name; ?> Center</p>
                                    <p class="text-sm text-gray-500">Surat, India</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form Section -->
            <div class="form-section">
                <div class="bg-white dark:bg-gray-900 rounded-lg shadow shadow-gray-100 dark:shadow-gray-800 p-6">
                    <div class="grid md:grid-cols-2 grid-cols-1 gap-8">
                        <!-- Left: Introduction -->
                        <div>
                            <h4 class="text-xl font-semibold mb-4">Get in touch with us</h4>
                            <p class="text-gray-400 mb-4">
                                Have questions about courses, need support, or want to partner with us?
                                We're here to help you.
                            </p>
                            <p class="text-gray-400 mb-6">
                                Send us a message and our team will respond within 24 hours.
                            </p>

                            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                                <p class="text-gray-400 mb-3">Follow us:</p>
                                <div class="flex space-x-3">
                                    <a href="#"
                                        class="size-9 inline-flex items-center justify-center bg-primary/10 hover:bg-primary text-primary hover:text-white rounded-lg">
                                        <i class="ri-facebook-fill"></i>
                                    </a>
                                    <a href="#"
                                        class="size-9 inline-flex items-center justify-center bg-primary/10 hover:bg-primary text-primary hover:text-white rounded-lg">
                                        <i class="ri-twitter-fill"></i>
                                    </a>
                                    <a href="#"
                                        class="size-9 inline-flex items-center justify-center bg-primary/10 hover:bg-primary text-primary hover:text-white rounded-lg">
                                        <i class="ri-instagram-line"></i>
                                    </a>
                                    <a href="#"
                                        class="size-9 inline-flex items-center justify-center bg-primary/10 hover:bg-primary text-primary hover:text-white rounded-lg">
                                        <i class="ri-linkedin-box-fill"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Form -->
                        <div>
                            <?php if (!empty($message)): ?>
                                <div
                                    class="p-4 rounded-lg mb-4 <?php echo $message_type === 'success' ? 'bg-green-50 text-green-800 dark:bg-green-900/20 dark:text-green-300' : 'bg-red-50 text-red-800 dark:bg-red-900/20 dark:text-red-300'; ?>">
                                    <div class="flex items-center">
                                        <i
                                            class="ri-<?php echo $message_type === 'success' ? 'checkbox-circle-line' : 'error-warning-line'; ?> me-2"></i>
                                        <span><?php echo htmlspecialchars($message); ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <form method="post" name="contactForm" class="space-y-4">
                                <div class="grid md:grid-cols-2 grid-cols-1 gap-4">
                                    <div>
                                        <label class="text-gray-600 dark:text-gray-300 mb-2 block">Your Name</label>
                                        <input type="text" name="name"
                                            class="w-full h-10 mb-1 px-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900"
                                            placeholder="John Doe"
                                            value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                                    </div>

                                    <div>
                                        <label class="text-gray-600 dark:text-gray-300 mb-2 block">Email Address</label>
                                        <input type="email" name="email"
                                            class="w-full h-10 mb-1 px-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900"
                                            placeholder="john@example.com"
                                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                    </div>
                                </div>

                                <div>
                                    <label class="text-gray-600 dark:text-gray-300 mb-2 block">Subject</label>
                                    <input type="text" name="subject"
                                        class="w-full h-10 mb-1 px-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900"
                                        placeholder="How can we help you?"
                                        value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>">
                                </div>

                                <div>
                                    <label class="text-gray-600 dark:text-gray-300 mb-2 block">Your Message</label>
                                    <textarea name="message" rows="3"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 resize-none"
                                        placeholder="Tell us about your inquiry..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                                </div>

                                <button type="submit"
                                    class="h-10 w-full px-4 text-sm font-medium rounded-lg bg-primary hover:bg-primary-dark text-white">
                                    <i class="ri-send-plane-line me-2"></i> Send Message
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQ Section - Dynamic from Database -->
            <?php if (!empty($faqs)): ?>
                <div class="faq-section">
                    <div class="text-center mb-8">
                        <h4 class="mb-3 text-2xl font-semibold">Frequently Asked Questions</h4>
                        <p class="text-gray-400 max-w-xl mx-auto">Find quick answers to common questions about
                            <?php echo $company_name; ?>.
                        </p>
                    </div>

                    <div id="accordion-collapseone" data-accordion="collapse" class="mt-6">
                        <?php
                        $faq_counter = 1;
                        foreach ($faqs as $faq):
                            ?>
                            <div
                                class="relative shadow-sm shadow-gray-200 dark:shadow-gray-700 bg-white dark:bg-gray-900 rounded-lg overflow-hidden mb-4">
                                <h2 class="font-medium" id="accordion-collapse-heading-<?php echo $faq_counter; ?>">
                                    <button type="button"
                                        class="flex justify-between items-center p-4 w-full font-medium text-start cursor-pointer"
                                        data-accordion-target="#accordion-collapse-body-<?php echo $faq_counter; ?>"
                                        aria-expanded="<?php echo $faq_counter === 1 ? 'true' : 'false'; ?>"
                                        aria-controls="accordion-collapse-body-<?php echo $faq_counter; ?>">
                                        <span><?php echo htmlspecialchars($faq['question']); ?></span>
                                        <svg data-accordion-icon
                                            class="size-4 <?php echo $faq_counter === 1 ? 'rotate-180' : ''; ?> shrink-0"
                                            fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </h2>
                                <div id="accordion-collapse-body-<?php echo $faq_counter; ?>"
                                    class="<?php echo $faq_counter === 1 ? '' : 'hidden'; ?>"
                                    aria-labelledby="accordion-collapse-heading-<?php echo $faq_counter; ?>">
                                    <div class="p-4 border-t border-gray-100 dark:border-gray-800">
                                        <p class="text-gray-400 dark:text-gray-400">
                                            <?php echo htmlspecialchars($faq['answer']); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?php
                            $faq_counter++;
                        endforeach;
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <!-- End Contact Section -->

    <?php include 'footer.php'; ?>

    <!-- Back to top -->
    <a href="#" onclick="topFunction()" id="back-to-top"
        class="back-to-top fixed hidden text-lg rounded-lg z-10 bottom-5 end-5 size-9 text-center bg-primary/10 hover:bg-primary text-primary hover:text-white leading-9">
        <i class="ri-arrow-up-line"></i>
    </a>

    <!-- JAVASCRIPTS -->
    <script src="assets/js/plugins.init.js"></script>
    <script src="assets/js/app.js"></script>

    <script>
        // Simple form validation
        document.forms.contactForm.addEventListener('submit', function (e) {
            const name = this.name.value.trim();
            const email = this.email.value.trim();
            const subject = this.subject.value.trim();
            const message = this.message.value.trim();

            if (!name) {
                Swal.fire('Error', 'Please enter your name.', 'error');
                e.preventDefault();
                this.name.focus();
                return false;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                Swal.fire('Error', 'Please enter a valid email address.', 'error');
                e.preventDefault();
                this.email.focus();
                return false;
            }

            if (!subject) {
                Swal.fire('Error', 'Please enter a subject.', 'error');
                e.preventDefault();
                this.subject.focus();
                return false;
            }

            if (!message || message.length < 10) {
                Swal.fire('Error', 'Please enter a message with at least 10 characters.', 'error');
                e.preventDefault();
                this.message.focus();
                return false;
            }

            return true;
        });

        // Initialize FAQ accordion
        document.addEventListener('DOMContentLoaded', function () {
            const accordionButtons = document.querySelectorAll('[data-accordion-target]');

            accordionButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const targetId = this.getAttribute('data-accordion-target');
                    const target = document.querySelector(targetId);
                    const icon = this.querySelector('[data-accordion-icon]');

                    if (target.classList.contains('hidden')) {
                        target.classList.remove('hidden');
                        icon.classList.add('rotate-180');
                    } else {
                        target.classList.add('hidden');
                        icon.classList.remove('rotate-180');
                    }
                });
            });
        });
    </script>
</body>

</html>