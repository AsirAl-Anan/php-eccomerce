<?php
// terms-of-use.php
$pageTitle = "Terms of Use";
include('header.php');
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6"><?php echo $pageTitle; ?></h1>
    
    <div class="prose max-w-none">
        <h2 class="text-2xl font-semibold mt-6 mb-4">1. Acceptance of Terms</h2>
        <p class="mb-4">By accessing and using [Your E-commerce Site Name] (the "Site"), you agree to be bound by these Terms of Use and all applicable laws and regulations. If you do not agree with any part of these terms, you may not use our Site.</p>

        <h2 class="text-2xl font-semibold mt-6 mb-4">2. Use of the Site</h2>
        <p class="mb-4">You may use our Site for lawful purposes only. You are prohibited from violating or attempting to violate the security of the Site, including, without limitation:</p>
        <ul class="list-disc pl-6 mb-4">
            <li>Accessing data not intended for you or logging into a server or account which you are not authorized to access;</li>
            <li>Attempting to probe, scan, or test the vulnerability of a system or network;</li>
            <li>Attempting to interfere with service to any user, host, or network.</li>
        </ul>

        <h2 class="text-2xl font-semibold mt-6 mb-4">3. Product Information</h2>
        <p class="mb-4">We strive to provide accurate product information, but we do not warrant that product descriptions or other content is accurate, complete, reliable, current, or error-free.</p>

        <h2 class="text-2xl font-semibold mt-6 mb-4">4. Pricing and Availability</h2>
        <p class="mb-4">All prices are subject to change without notice. We reserve the right to modify or discontinue any product without notice. We shall not be liable to you or any third party for any modification, price change, or discontinuance of any product.</p>

        <!-- Add more sections as needed -->

    </div>
</div>

<?php include('footer.php'); ?>