<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" {{ $attributes }}>
    <defs>
        <linearGradient id="scissorGrad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#243B53"/>
            <stop offset="100%" stop-color="#334E68"/>
        </linearGradient>
    </defs>
    <!-- Lâminas da tesoura -->
    <path d="M30 25 C20 25 15 35 15 42 C15 50 20 55 30 55 C35 55 38 52 40 48 L60 30" 
          stroke="url(#scissorGrad)" stroke-width="4" fill="none" stroke-linecap="round"/>
    <path d="M70 25 C80 25 85 35 85 42 C85 50 80 55 70 55 C65 55 62 52 60 48 L40 30" 
          stroke="url(#scissorGrad)" stroke-width="4" fill="none" stroke-linecap="round"/>
    <path d="M30 75 C20 75 15 65 15 58 C15 50 20 45 30 45 C35 45 38 48 40 52 L60 70" 
          stroke="url(#scissorGrad)" stroke-width="4" fill="none" stroke-linecap="round"/>
    <path d="M70 75 C80 75 85 65 85 58 C85 50 80 45 70 45 C65 45 62 48 60 52 L40 70" 
          stroke="url(#scissorGrad)" stroke-width="4" fill="none" stroke-linecap="round"/>
    <!-- Pivô central -->
    <circle cx="50" cy="50" r="4" fill="#102A43"/>
</svg>
