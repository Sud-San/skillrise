<?php
	session_start();
	include 'connection.php';
?>
<!DOCTYPE html>
<html lang="en" class="light scroll-smooth" dir="ltr">
    
<!-- dream class  terms.html     -->
	<head>
        <?php include 'headtag.php';?>
    </head>
    
    <body class="text-gray-900 dark:text-white dark:bg-gray-900">
        <?php include 'header.php';?>
       

        <!-- Start Hero -->
        <section class="relative table bg-primary w-full py-24">
            <div class="absolute inset-0 bg-[url('../assets/images/bg/box.html')] bg-no-repeat bg-center bg-cover"></div>
            <div class="container relative">
                <div class="grid grid-cols-1 text-center mt-10">
                    <h3 class="text-4xl md:leading-normal leading-normal font-semibold text-white">Terms & Services</h3>
                    
                    <ul class="tracking-[0.5px] inline-block mt-2">
                        <li class="inline-block font-medium uppercase duration-500 text-xs ease-in-out text-white/70 dark:text-white/50 hover:text-white dark:hover:text-white"><a href="index.php"><?php echo $company_name; ?></a></li>
                        <li class="inline-block text-white/70 dark:text-white/50 mx-0.5 text-sm ltr:rotate-0 rtl:rotate-180"><i class="ri-arrow-right-s-line"></i></li>
                        <li class="inline-block font-medium uppercase duration-500 text-xs ease-in-out text-white dark:text-white" aria-current="page">Terms</li>
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
                            <h5 class="text-xl font-semibold mb-4">Introduction :</h5>
                            <p class="text-gray-400">It seems that only fragments of the original text remain in the Lorem Ipsum texts used today. One may speculate that over the course of time certain letters were added or deleted at various positions within the text.</p>

                            <h5 class="text-xl font-semibold mb-4 mt-6">User Agreements :</h5>
                            <p class="text-gray-400">The most well-known dummy text is the 'Lorem Ipsum', which is said to have <b class="text-red-600">originated</b> in the 16th century. Lorem Ipsum is <b class="text-red-600">composed</b> in a pseudo-Latin language which more or less <b class="text-red-600">corresponds</b> to 'proper' Latin. It contains a series of real Latin words. This ancient dummy text is also <b class="text-red-600">incomprehensible</b>, but it imitates the rhythm of most European languages in Latin script. The <b class="text-red-600">advantage</b> of its Latin origin and the relative <b class="text-red-600">meaninglessness</b> of Lorum Ipsum is that the text does not attract attention to itself or distract the viewer's <b class="text-red-600">attention</b> from the layout.</p>
                            <p class="text-gray-400 mt-3">There is now an <b class="text-red-600">abundance</b> of readable dummy texts. These are usually used when a text is <b class="text-red-600">required purely</b> to fill a space. These alternatives to the classic Lorem Ipsum texts are often amusing and tell short, funny or <b class="text-red-600">nonsensical</b> stories.</p>
                            <p class="text-gray-400 mt-3">It seems that only <b class="text-red-600">fragments</b> of the original text remain in the Lorem Ipsum texts used today. One may speculate that over the course of time certain letters were added or deleted at various positions within the text.</p>
                            
                            <h5 class="text-xl font-semibold mb-4 mt-6">Restrictions :</h5>
                            <p class="text-gray-400">You are specifically restricted from all of the following :</p>
                            <ul class="list-none text-gray-400 mt-3">
                                <li class="flex mt-2"><i class="ri-arrow-right-line text-primary align-middle me-2"></i>Digital Marketing Solutions for Tomorrow</li>
                                <li class="flex mt-2"><i class="ri-arrow-right-line text-primary align-middle me-2"></i>Our Talented & Experienced Marketing Agency</li>
                                <li class="flex mt-2"><i class="ri-arrow-right-line text-primary align-middle me-2"></i>Create your own skin to match your brand</li>
                                <li class="flex mt-2"><i class="ri-arrow-right-line text-primary align-middle me-2"></i>Digital Marketing Solutions for Tomorrow</li>
                                <li class="flex mt-2"><i class="ri-arrow-right-line text-primary align-middle me-2"></i>Our Talented & Experienced Marketing Agency</li>
                                <li class="flex mt-2"><i class="ri-arrow-right-line text-primary align-middle me-2"></i>Create your own skin to match your brand</li>
                            </ul>

                            <h5 class="text-xl font-semibold mt-6">Users Question & Answer :</h5>

                            <div id="accordion-collapse" data-accordion="collapse" class="mt-6">
                                <div class="relative shadow-sm shadow-gray-200 dark:shadow-gray-700 bg-white dark:bg-gray-900 rounded-lg overflow-hidden mt-4">
                                    <h2 class="text-base font-medium" id="accordion-collapse-heading-1">
                                        <button type="button" class="flex justify-between items-center p-5 w-full font-medium text-start cursor-pointer" data-accordion-target="#accordion-collapse-body-1" aria-expanded="true" aria-controls="accordion-collapse-body-1">
                                            <span>How does it work ?</span>
                                            <svg data-accordion-icon class="size-5 rotate-180 shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                    </h2>
                                    <div id="accordion-collapse-body-1" class="hidden" aria-labelledby="accordion-collapse-heading-1">
                                        <div class="p-5 border-t border-gray-100 dark:border-gray-800">
                                        <p class="text-gray-400 dark:text-gray-400">There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="relative shadow-sm shadow-gray-200 dark:shadow-gray-700 bg-white dark:bg-gray-900 rounded-lg overflow-hidden mt-4">
                                    <h2 class="text-base font-medium" id="accordion-collapse-heading-2">
                                        <button type="button" class="flex justify-between items-center p-5 w-full font-medium text-start cursor-pointer" data-accordion-target="#accordion-collapse-body-2" aria-expanded="false" aria-controls="accordion-collapse-body-2">
                                            <span>Do I need a designer to use <?php echo $company_name; ?> ?</span>
                                            <svg data-accordion-icon class="size-5 shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                    </h2>
                                    <div id="accordion-collapse-body-2" class="hidden" aria-labelledby="accordion-collapse-heading-2">
                                        <div class="p-5 border-t border-gray-100 dark:border-gray-800">
                                        <p class="text-gray-400 dark:text-gray-400">There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="relative shadow-sm shadow-gray-200 dark:shadow-gray-700 bg-white dark:bg-gray-900 rounded-lg overflow-hidden mt-4">
                                    <h2 class="text-base font-medium" id="accordion-collapse-heading-3">
                                        <button type="button" class="flex justify-between items-center p-5 w-full font-medium text-start cursor-pointer" data-accordion-target="#accordion-collapse-body-3" aria-expanded="false" aria-controls="accordion-collapse-body-3">
                                            <span>What do I need to do to start selling ?</span>
                                            <svg data-accordion-icon class="size-5 shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                    </h2>
                                    <div id="accordion-collapse-body-3" class="hidden" aria-labelledby="accordion-collapse-heading-3">
                                        <div class="p-5 border-t border-gray-100 dark:border-gray-800">
                                        <p class="text-gray-400 dark:text-gray-400">There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form.</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="relative shadow-sm shadow-gray-200 dark:shadow-gray-700 bg-white dark:bg-gray-900 rounded-lg overflow-hidden mt-4">
                                    <h2 class="text-base font-medium" id="accordion-collapse-heading-4">
                                        <button type="button" class="flex justify-between items-center p-5 w-full font-medium text-start cursor-pointer" data-accordion-target="#accordion-collapse-body-4" aria-expanded="false" aria-controls="accordion-collapse-body-4">
                                            <span>What happens when I receive an order ?</span>
                                            <svg data-accordion-icon class="size-5 shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                    </h2>
                                    <div id="accordion-collapse-body-4" class="hidden" aria-labelledby="accordion-collapse-heading-4">
                                        <div class="p-5 border-t border-gray-100 dark:border-gray-800">
                                        <p class="text-gray-400 dark:text-gray-400">There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6">
                                <a href="#" class="h-11 px-5 tracking-wider inline-flex justify-center items-center text-sm font-medium rounded-lg bg-primary text-white">Accept</a>
                                <a href="#" class="h-11 px-5 tracking-wider inline-flex justify-center items-center text-sm font-medium rounded-lg bg-primary/10 hover:bg-primary text-primary hover:text-white ms-2">Decline</a>
                            </div>
                        </div>
                    </div><!--end -->
                </div><!--end grid-->
            </div><!--end container-->
        </section><!--end section-->
        <!-- End Terms & Conditions -->

		
		<?php include 'footer.php'?>

        <!-- Back to top -->
        <a href="#" onclick="topFunction()" id="back-to-top" class="back-to-top fixed hidden text-lg rounded-lg z-10 bottom-5 end-5 size-9 text-center bg-primary/10 hover:bg-primary text-primary hover:text-white leading-9"><i class="ri-arrow-up-line"></i></a>
        <!-- Back to top -->


        <!-- JAVASCRIPTS -->
        <script src="assets/js/plugins.init.js"></script>
        <script src="assets/js/app.js"></script>
        <!-- JAVASCRIPTS -->
    </body>

<!-- dream class  terms.html     -->
</html>