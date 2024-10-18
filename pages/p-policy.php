<?php
// privacy-policy.php
$pageTitle = "Privacy Policy";
include('header.php');
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6"><?php echo $pageTitle; ?></h1>
    
    <div class="prose max-w-none">
        <p class="mb-4">This Privacy Policy describes how [Your E-commerce Site Name] ("we," "us," or "our") collects, uses, and shares your personal information when you visit or make a purchase from our site.</p>

        <h2 class="text-2xl font-semibold mt-6 mb-4">1. Information We Collect</h2>
        <p class="mb-4">When you visit the Site, we automatically collect certain information about your device, including information about your web browser, IP address, time zone, and some of the cookies that are installed on your device.</p>

        <h2 class="text-2xl font-semibold mt-6 mb-4">2. How We Use Your Information</h2>
        <p class="mb-4">We use the information that we collect to:</p>
        <ul class="list-disc pl-6 mb-4">
            <li>Process your orders and to provide you with invoices and/or order confirmations;</li>
            <li>Communicate with you about our products, services, and promotions;</li>
            <li>Screen our orders for potential risk or fraud;</li>
            <li>Improve and optimize our Site and services.</li>
        </ul>

        <h2 class="text-2xl font-semibold mt-6 mb-4">3. Sharing Your Information</h2>
        <p class="mb-4">We share your Personal Information with service providers to help us provide our services and fulfill our contracts with you. For example:</p>
        <ul class="list-disc pl-6 mb-4">
            <li>Payment processors</li>
            <li>Shipping carriers</li>
            <li>Marketing and analytics services</li>
        </ul>

        <!-- Add more sections as needed -->

    </div>
</div>

<?php include('footer.php'); ?>