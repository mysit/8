document.addEventListener('DOMContentLoaded', function() {
    const slider = document.querySelector('.slider');
    const slides = document.querySelectorAll('.slide');
    const pagerDots = document.querySelector('.pager-dots');
    const currentPageElement = document.getElementById('current-page');
    const totalPagesElement = document.getElementById('total-pages');
    const prevBtn = document.querySelector('.btn-prev');
    const nextBtn = document.querySelector('.btn-next');
    
    let currentSlide = 0;
    let slidesPerView = 3;
    
    function updateSlidesPerView() {
        slidesPerView = window.innerWidth <= 768 ? 1 : 3;
        updateSlider();
        updatePager();
    }
    
    function initPager() {
        if (!pagerDots) return;
        pagerDots.innerHTML = '';
        const totalPages = Math.ceil(slides.length / slidesPerView);
        if (totalPagesElement) totalPagesElement.textContent = totalPages;
        
        for (let i = 0; i < totalPages; i++) {
            const dot = document.createElement('div');
            dot.className = 'dot';
            if (i === 0) dot.classList.add('active');
            dot.addEventListener('click', () => goToSlide(i * slidesPerView));
            pagerDots.appendChild(dot);
        }
    }
    
    function updateSlider() {
        if (!slider) return;
        const slideWidth = 100 / slidesPerView;
        const translateX = -currentSlide * slideWidth;
        slider.style.transform = `translateX(${translateX}%)`;
    }
    
    function updatePager() {
        const dots = document.querySelectorAll('.dot');
        const currentPage = Math.floor(currentSlide / slidesPerView);
        dots.forEach((dot, index) => dot.classList.toggle('active', index === currentPage));
        if (currentPageElement) currentPageElement.textContent = currentPage + 1;
    }
    
    function goToSlide(slideIndex) {
        const maxSlide = slides.length - slidesPerView;
        currentSlide = Math.max(0, Math.min(slideIndex, maxSlide));
        updateSlider();
        updatePager();
    }
    
    function nextSlide() {
        const maxSlide = slides.length - slidesPerView;
        currentSlide = (currentSlide < maxSlide) ? currentSlide + slidesPerView : 0;
        updateSlider();
        updatePager();
    }
    
    function prevSlide() {
        if (currentSlide > 0) {
            currentSlide -= slidesPerView;
        } else {
            currentSlide = Math.max(0, slides.length - slidesPerView);
        }
        updateSlider();
        updatePager();
    }
    
    if (prevBtn) prevBtn.addEventListener('click', prevSlide);
    if (nextBtn) nextBtn.addEventListener('click', nextSlide);
    window.addEventListener('resize', () => { updateSlidesPerView(); initPager(); });
    
    updateSlidesPerView();
    initPager();
});
