<footer class="mt-auto shadow-lg bg-primary" role="contentinfo">
    <div class="container mx-auto px-2">
        <div class="flex justify-center items-center py-2 md:py-4">
            <!-- Main Content -->
            <div class="text-center w-full max-w-4xl">
                <div class="mb-2">
                    <h6 class="text-white font-bold mb-2 text-lg md:text-xl">
                        <i class="fas fa-utensils mr-2"></i>$SiteConfig.Title
                    </h6>
                    <p class="mb-0 text-white/75 text-sm md:text-base">$SiteConfig.Credit</p>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
.hover-opacity-100:hover {
    opacity: 1 !important;
    transform: translateY(-2px);
    transition: all 0.3s ease;
}

footer .fab:hover, footer .fas:hover {
    color: #F3C623 !important;
    transform: scale(1.1);
    transition: all 0.3s ease;
}
</style>