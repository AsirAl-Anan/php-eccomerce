<?php
// cookie-policy.php
$pageTitle = "Cookie Policy";
include('header.php');
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6"><?php echo $pageTitle; ?></h1>
    
    <div class="prose max-w-none">
        <p class="mb-4">This Cookie Policy explains how [Your E-commerce Site Name] ("we," "us," or "our") uses cookies and similar technologies to recognize you when you visit our website. It explains what these technologies are and why we use them, as well as your rights to control our use of them.</p>

        <h2 class="text-2xl font-semibold mt-6 mb-4">1. What are cookies?</h2>
        <p class="mb-4">Cookies are small data files that are placed on your computer or mobile device when you visit a website. Cookies are widely used by website owners in order to make their websites work, or to work more efficiently, as well as to provide reporting information.</p>

        <h2 class="text-2xl font-semibold mt-6 mb-4">2. Why do we use cookies?</h2>
        <p class="mb-4">We use first-party and third-party cookies for several reasons. Some cookies are required for technical reasons in order for our website to operate, and we refer to these as "essential" or "strictly necessary" cookies. Other cookies enable us to track and target the interests of our users to enhance the experience on our website. Third parties serve cookies through our website for advertising, analytics, and other purposes.</p>

        <h2 class="text-2xl font-semibold mt-6 mb-4">3. Types of cookies we use</h2>
        <ul class="list-disc pl-6 mb-4">
            <li><strong>Essential website cookies:</strong> These cookies are strictly necessary to provide you with services available through our website and to use some of its features.</li>
            <li><strong>Performance and functionality cookies:</strong> These cookies are used to enhance the performance and functionality of our website but are non-essential to their use.</li>
            <li><strong>Analytics and customization cookies:</strong> These cookies collect information that is used either in aggregate form to help us understand how our website is being used or how effective our marketing campaigns are, or to help us customize our website for you.</li>
            <li><strong>Advertising cookies:</strong> These cookies are used to make advertising messages more relevant to you.</li>
        </ul>

        <!-- Add more sections as needed -->

    </div>
</div>

<?php include('footer.php'); ?>