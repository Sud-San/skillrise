<?php
	session_start();
	include 'connection.php';
?>

<!DOCTYPE html>
<html lang="en" class="light scroll-smooth" dir="ltr">
    <head>
        <?php include 'headtag.php';?>

    </head>
    
    <body class="text-gray-900 dark:text-white dark:bg-gray-900">
        
        <?php include 'header.php';?>

        <!-- Start Hero -->
        <section class="relative table bg-primary w-full py-24">
            <div class="absolute inset-0 bg-[url('../../assets/images/bg/box.png')] bg-no-repeat bg-center bg-cover"></div>
            <div class="container relative">
                <div class="grid grid-cols-1 text-center mt-10">
                    <h3 class="text-4xl md:leading-normal leading-normal font-semibold text-white">Privacy Policy</h3>
                    
                    <ul class="tracking-[0.5px] inline-block mt-2">
                        <li class="inline-block font-medium uppercase duration-500 text-xs ease-in-out text-white/70 dark:text-white/50 hover:text-white dark:hover:text-white"><a href="index.php"><?php echo $company_name; ?></a></li>
                        <li class="inline-block text-white/70 dark:text-white/50 mx-0.5 text-sm ltr:rotate-0 rtl:rotate-180"><i class="ri-arrow-right-s-line"></i></li>
                        <li class="inline-block font-medium uppercase duration-500 text-xs ease-in-out text-white dark:text-white" aria-current="page">Privacy Policy</li>
                    </ul>
                </div><!--end grid-->
            </div><!--end container-->
        </section><!--end section-->
        <!-- End Hero -->

        <!-- Start Terms & Conditions -->
        <section class="relative lg:py-24 py-16">
            <div class="container relative">
                <div class="md:flex justify-center">
                    <div class="md:w-3/4">
                        <div class="p-6 bg-white dark:bg-gray-900 shadow-sm shadow-gray-100 dark:shadow-gray-800 rounded-lg">
                            <h5 class="text-xl font-semibold mb-4">Overview :</h5>
                            <p class="text-gray-400">It seems that only fragments of the original text remain in the Lorem Ipsum texts used today. One may speculate that over the course of time certain letters were added or deleted at various positions within the text.</p>
                            <p class="text-gray-400">In the 1960s, the text suddenly became known beyond the professional circle of typesetters and layout designers when it was used for Letraset sheets (adhesive letters on transparent film, popular until the 1980s) Versions of the text were subsequently included in DTP programmes such as PageMaker etc.</p>
                            <p class="text-gray-400">There is now an abundance of readable dummy texts. These are usually used when a text is required purely to fill a space. These alternatives to the classic Lorem Ipsum texts are often amusing and tell short, funny or nonsensical stories.</p>
                        
                            <h5 class="text-xl font-semibold mb-4 mt-6">We use your information to :</h5>
                            <ul class="list-unstyled text-gray-400 mt-4">
                                <li class="flex mt-2"><i class="ri-arrow-right-line text-primary align-middle me-2"></i>Digital Marketing Solutions for Tomorrow</li>
                                <li class="flex mt-2"><i class="ri-arrow-right-line text-primary align-middle me-2"></i>Our Talented & Experienced Marketing Agency</li>
                                <li class="flex mt-2"><i class="ri-arrow-right-line text-primary align-middle me-2"></i>Create your own skin to match your brand</li>
                                <li class="flex mt-2"><i class="ri-arrow-right-line text-primary align-middle me-2"></i>Digital Marketing Solutions for Tomorrow</li>
                                <li class="flex mt-2"><i class="ri-arrow-right-line text-primary align-middle me-2"></i>Our Talented & Experienced Marketing Agency</li>
                                <li class="flex mt-2"><i class="ri-arrow-right-line text-primary align-middle me-2"></i>Create your own skin to match your brand</li>
                            </ul>

                            <h5 class="text-xl font-semibold mb-4 mt-6">Information Provided Voluntarily :</h5>
                            <p class="text-gray-400">In the 1960s, the text suddenly became known beyond the professional circle of typesetters and layout designers when it was used for Letraset sheets (adhesive letters on transparent film, popular until the 1980s) Versions of the text were subsequently included in DTP programmes such as PageMaker etc.</p>

                            <div class="mt-6">
                                <a href="" class="h-11 px-5 tracking-wider inline-flex justify-center items-center text-sm font-medium rounded-lg bg-primary text-white">Print</a>
                            </div>
                        </div>
                    </div><!--end -->
                </div><!--end grid-->
            </div><!--end container-->
        </section><!--end section-->
        <!-- End Terms & Conditions -->

		<?php include 'footer.php';?>

        <!-- Back to top -->
        <a href="#" onclick="topFunction()" id="back-to-top" class="back-to-top fixed hidden text-lg rounded-lg z-10 bottom-5 end-5 size-9 text-center bg-primary/10 hover:bg-primary text-primary hover:text-white leading-9"><i class="ri-arrow-up-line"></i></a>
        <!-- Back to top -->

        <!-- JAVASCRIPTS -->
        <script src="assets/js/plugins.init.js"></script>
        <script src="assets/js/app.js"></script>
        <!-- JAVASCRIPTS -->
    </body>
</html>