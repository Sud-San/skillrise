
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
                    <h3 class="text-4xl md:leading-normal leading-normal font-semibold text-white">Pricing Plans</h3>
                    
                    <ul class="tracking-[0.5px] inline-block mt-2">
                        <li class="inline-block font-medium uppercase duration-500 text-xs ease-in-out text-white/70 dark:text-white/50 hover:text-white dark:hover:text-white"><a href="index.php"><?php echo $company_name; ?></a></li>
                        <li class="inline-block text-white/70 dark:text-white/50 mx-0.5 text-sm ltr:rotate-0 rtl:rotate-180"><i class="ri-arrow-right-s-line"></i></li>
                        <li class="inline-block font-medium uppercase duration-500 text-xs ease-in-out text-white dark:text-white" aria-current="page">Pricing</li>
                    </ul>
                </div><!--end grid-->
            </div><!--end container-->
        </section><!--end section-->
        <!-- End Hero -->

        <!-- Start -->
        <section class="relative lg:py-24 py-16">
            <div class="container relative">
                <div class="grid md:grid-cols-3 grid-cols-1 gap-6">
                    <div class="group relative rounded-lg shadow-sm hover:shadow-lg shadow-gray-100 dark:shadow-gray-800 bg-white dark:bg-gray-900 duration-500 z-2">
                        <div class="p-6">
                            <div class="text-center">
                                <h6 class="font-semibold uppercase mb-5 text-primary">Basic</h6>
    
                                <div class="flex justify-center">
                                    <span class="text-lg font-semibold">$</span>
                                    <span class="text-5xl font-semibold">0</span>
                                    <span class="text-sm font-medium self-end mb-1">/ month</span>
                                </div>
                            </div>

                            <ul class="list-none text-gray-400 mt-6">
                                <li class="flex items-center"><span class="inline-flex justify-center items-center size-5 rounded-full bg-primary/5 text-primary border border-primary/20 me-2"><i class="ri-check-line"></i></span> Marketing strategy</li>
                                <li class="mt-4 flex items-center"><span class="inline-flex justify-center items-center size-5 rounded-full bg-primary/5 text-primary border border-primary/20 me-2"><i class="ri-check-line"></i></span> Monthly management</li>
                                <li class="mt-4 flex items-center"><span class="inline-flex justify-center items-center size-5 rounded-full bg-primary/5 text-primary border border-primary/20 me-2"><i class="ri-check-line"></i></span> Social media share audit</li>
                                <li class="mt-4 flex items-center"><span class="inline-flex justify-center items-center size-5 rounded-full bg-primary/5 text-primary border border-primary/20 me-2"><i class="ri-check-line"></i></span> Competitive work analysis</li>
                                <li class="mt-4 flex items-center"><span class="inline-flex justify-center items-center size-5 rounded-full bg-primary/5 text-primary border border-primary/20 me-2"><i class="ri-check-line"></i></span> Get 30 day free trial</li>
                                <li class="mt-4 flex items-center"><span class="inline-flex justify-center items-center size-5 rounded-full bg-primary/5 text-primary border border-primary/20 me-2"><i class="ri-check-line"></i></span> No any hidden fees pay</li>
                            </ul>

                            <div class="mt-5 text-center">
                                <a href="" class="h-11 px-5 tracking-wider inline-flex items-center text-sm justify-center font-semibold rounded-full bg-primary text-white w-full">Get Started</a>
                            </div>
                        </div>
                    </div>

                    <div class="group relative rounded-lg shadow-sm hover:shadow-lg shadow-gray-100 dark:shadow-gray-800 bg-primary duration-500 z-2">
                        <div class="p-6">
                            <div class="text-center">
                                <h6 class="font-semibold uppercase mb-5 text-white">Standard</h6>
    
                                <div class="flex justify-center">
                                    <span class="text-lg font-semibold text-white/70">$</span>
                                    <span class="text-5xl font-semibold text-white">39</span>
                                    <span class="text-sm font-medium self-end text-white/70 mb-1">/ month</span>
                                </div>
                            </div>

                            <ul class="list-none text-white mt-6">
                                <li class="flex items-center"><span class="inline-flex justify-center items-center size-5 rounded-full bg-white/10 text-white border border-white/20 me-2"><i class="ri-check-line"></i></span> Marketing strategy</li>
                                <li class="mt-4 flex items-center"><span class="inline-flex justify-center items-center size-5 rounded-full bg-white/10 text-white border border-white/20 me-2"><i class="ri-check-line"></i></span> Monthly management</li>
                                <li class="mt-4 flex items-center"><span class="inline-flex justify-center items-center size-5 rounded-full bg-white/10 text-white border border-white/20 me-2"><i class="ri-check-line"></i></span> Social media share audit</li>
                                <li class="mt-4 flex items-center"><span class="inline-flex justify-center items-center size-5 rounded-full bg-white/10 text-white border border-white/20 me-2"><i class="ri-check-line"></i></span> Competitive work analysis</li>
                                <li class="mt-4 flex items-center"><span class="inline-flex justify-center items-center size-5 rounded-full bg-white/10 text-white border border-white/20 me-2"><i class="ri-check-line"></i></span> Get 30 day free trial</li>
                                <li class="mt-4 flex items-center"><span class="inline-flex justify-center items-center size-5 rounded-full bg-white/10 text-white border border-white/20 me-2"><i class="ri-check-line"></i></span> No any hidden fees pay</li>
                            </ul>

                            <div class="mt-5 text-center">
                                <a href="" class="h-11 px-5 tracking-wider inline-flex items-center text-sm justify-center font-semibold rounded-full bg-white text-primary w-full">Get Started</a>
                            </div>
                        </div>
                    </div>

                    <div class="group relative rounded-lg shadow-sm hover:shadow-lg shadow-gray-100 dark:shadow-gray-800 bg-white dark:bg-gray-900 duration-500 z-2">
                        <div class="p-6">
                            <div class="text-center">
                                <h6 class="font-semibold uppercase mb-5 text-primary">Professional</h6>
    
                                <div class="flex justify-center">
                                    <span class="text-lg font-semibold">$</span>
                                    <span class="text-5xl font-semibold">99</span>
                                    <span class="text-sm font-medium self-end mb-1">/ month</span>
                                </div>
                            </div>

                            <ul class="list-none text-gray-400 mt-6">
                                <li class="flex items-center"><span class="inline-flex justify-center items-center size-5 rounded-full bg-primary/5 text-primary border border-primary/20 me-2"><i class="ri-check-line"></i></span> Marketing strategy</li>
                                <li class="mt-4 flex items-center"><span class="inline-flex justify-center items-center size-5 rounded-full bg-primary/5 text-primary border border-primary/20 me-2"><i class="ri-check-line"></i></span> Monthly management</li>
                                <li class="mt-4 flex items-center"><span class="inline-flex justify-center items-center size-5 rounded-full bg-primary/5 text-primary border border-primary/20 me-2"><i class="ri-check-line"></i></span> Social media share audit</li>
                                <li class="mt-4 flex items-center"><span class="inline-flex justify-center items-center size-5 rounded-full bg-primary/5 text-primary border border-primary/20 me-2"><i class="ri-check-line"></i></span> Competitive work analysis</li>
                                <li class="mt-4 flex items-center"><span class="inline-flex justify-center items-center size-5 rounded-full bg-primary/5 text-primary border border-primary/20 me-2"><i class="ri-check-line"></i></span> Get 30 day free trial</li>
                                <li class="mt-4 flex items-center"><span class="inline-flex justify-center items-center size-5 rounded-full bg-primary/5 text-primary border border-primary/20 me-2"><i class="ri-check-line"></i></span> No any hidden fees pay</li>
                            </ul>

                            <div class="mt-5 text-center">
                                <a href="" class="h-11 px-5 tracking-wider inline-flex items-center text-sm justify-center font-semibold rounded-full bg-primary text-white w-full">Get Started</a>
                            </div>
                        </div>
                    </div>
                </div><!--end-->
            </div><!--end container-->

            <div class="container relative md:mt-24 mt-16">
                <div class="md:flex justify-center">
                    <div class="lg:w-3/4 md:w-4/5">
                        <div class="text-center pb-6">
                            <h4 class="mb-4 md:leading-normal text-3xl leading-normal font-semibold">Questions & Answers</h4>
                            <p class="text-gray-400 max-w-xl mx-auto"><?php echo $company_name; ?> offers a more personalized learning experience along with the flexibility of learning at your own pace.</p>
                        </div>           

                        <div id="accordion-collapseone" data-accordion="collapse" class="mt-6">
                            <div class="relative shadow-sm shadow-gray-200 dark:shadow-gray-700 bg-white dark:bg-gray-900 rounded-lg overflow-hidden">
                                <h2 class="font-medium" id="accordion-collapse-heading-1">
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
                                <h2 class="font-medium" id="accordion-collapse-heading-2">
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
                                <h2 class="font-medium" id="accordion-collapse-heading-3">
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
                                <h2 class="font-medium" id="accordion-collapse-heading-4">
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
                    </div>
                </div><!--end grid-->
            </div><!--end container-->
        </section><!--end section-->
        <!-- End -->


		 <?php include 'footer.php'?>

        <!-- Back to top -->
        <a href="#" onclick="topFunction()" id="back-to-top" class="back-to-top fixed hidden text-lg rounded-lg z-10 bottom-5 end-5 size-9 text-center bg-primary/10 hover:bg-primary text-primary hover:text-white leading-9"><i class="ri-arrow-up-line"></i></a>
        <!-- Back to top -->
 

        <!-- JAVASCRIPTS -->
        <script src="assets/js/plugins.init.js"></script>
        <script src="assets/js/app.js"></script>
        <!-- JAVASCRIPTS -->
    </body>
</html>