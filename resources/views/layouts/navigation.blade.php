@php
    $isCadastrosActive = request()->routeIs('admin.customers.*') || request()->routeIs('admin.services.*') || request()->routeIs('admin.products.*');
    $isFinanceiroActive = request()->routeIs('admin.financial.*') || request()->routeIs('admin.commissions.*') || request()->routeIs('admin.sales.*');
    $isWhatsAppActive = request()->routeIs('admin.settings.whatsapp') || request()->routeIs('admin.settings.evolution') || request()->routeIs('admin.settings.bot') || request()->routeIs('admin.bot-menu.*') || request()->routeIs('admin.bot-messages.*') || request()->routeIs('admin.blocked-numbers.*') || request()->routeIs('admin.webhook-logs.*');
@endphp

<nav x-data="{ open: false }" class="bg-white border-b border-gray-200 shadow-sm dark:bg-stone-800 dark:border-stone-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-10 w-auto" />
                    </a>
                </div>

                <div class="hidden space-x-8 sm:ml-10 sm:flex sm:items-stretch">
                    <div class="inline-flex items-center">
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            Dashboard
                        </x-nav-link>
                    </div>
                    <div class="inline-flex items-center">
                        <x-nav-link :href="route('admin.appointments.index')" :active="request()->routeIs('admin.appointments.*')">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Agenda
                        </x-nav-link>
                    </div>

                    {{-- Cadastros Dropdown --}}
                    <div class="inline-flex items-center">
                        <x-dropdown align="center" width="48">
                            <x-slot name="trigger">
                                <x-nav-link :active="$isCadastrosActive">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    <span>Cadastros</span>
                                    <svg class="fill-current h-4 w-4 ml-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </x-nav-link>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('admin.customers.index')" :active="request()->routeIs('admin.customers.*')">
                                    Clientes
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('admin.services.index')" :active="request()->routeIs('admin.services.*')">
                                    Serviços
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('admin.products.index')" :active="request()->routeIs('admin.products.*') && !request()->routeIs('admin.products.stock-report')">
                                    Produtos
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('admin.products.stock-report')" :active="request()->routeIs('admin.products.stock-report')">
                                    Relatório de Estoque
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    {{-- Financeiro Dropdown --}}
                    <div class="inline-flex items-center">
                        <x-dropdown align="center" width="48">
                            <x-slot name="trigger">
                                <x-nav-link :active="$isFinanceiroActive">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Financeiros</span>
                                    <svg class="fill-current h-4 w-4 ml-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </x-nav-link>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('admin.financial.index')" :active="request()->routeIs('admin.financial.*')">
                                    Financeiro
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('admin.commissions.index')" :active="request()->routeIs('admin.commissions.*')">
                                    Comissões
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('admin.sales.index')" :active="request()->routeIs('admin.sales.*')">
                                    Vendas
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                    <div class="inline-flex items-center">
                        <x-nav-link :href="route('admin.settings.working-hours')" :active="request()->routeIs('admin.settings.*') && !request()->routeIs('admin.settings.whatsapp') && !request()->routeIs('admin.settings.evolution') && !request()->routeIs('admin.settings.bot')">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Horários
                        </x-nav-link>
                    </div>
                    <div class="inline-flex items-center">
                        <x-nav-link :href="route('admin.loyalty.index')" :active="request()->routeIs('admin.loyalty.*')">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            Fidelidade
                        </x-nav-link>
                    </div>

                    {{-- WhatsApp Dropdown --}}
                    <div class="inline-flex items-center">
                        <x-dropdown align="center" width="48">
                            <x-slot name="trigger">
                                <x-nav-link :active="$isWhatsAppActive">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    <span>WhatsApp</span>
                                    <svg class="fill-current h-4 w-4 ml-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </x-nav-link>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('admin.settings.evolution')" :active="request()->routeIs('admin.settings.evolution')">
                                    Evolution API
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('admin.settings.bot')" :active="request()->routeIs('admin.settings.bot')">
                                    Configurar Bot
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('admin.bot-messages.index')" :active="request()->routeIs('admin.bot-messages.*')">
                                    Mensagens do Bot
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('admin.blocked-numbers.index')" :active="request()->routeIs('admin.blocked-numbers.*')">
                                    Bloquear Números
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('admin.webhook-logs.index')" :active="request()->routeIs('admin.webhook-logs.*')">
                                    Webhooks
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ml-6 shrink-0">
                <button @click="dark = !dark" class="mr-3 p-2 rounded-lg text-gray-400 hover:text-brand-600 hover:bg-brand-50 dark:text-stone-400 dark:hover:text-white dark:hover:bg-stone-700 transition-colors" title="Alternar tema">
                    <svg x-show="!dark" x-cloak class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <svg x-show="dark" x-cloak class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </button>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center text-sm font-medium text-gray-700 hover:text-brand-700 focus:outline-none focus:text-brand-700 transition duration-150 ease-in-out dark:text-stone-300 dark:hover:text-white">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ml-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                        <x-slot name="content">
                            @if(Auth::user()->isAdmin())
                            <x-dropdown-link :href="route('admin.users.index')">
                                Gerenciar Usuários
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.reports.index')">
                                Relatórios
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.backup.index')">
                                Backup
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.logs.index')">
                                Auditoria
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.settings.company')" :active="request()->routeIs('admin.settings.company')">
                                Empresa
                            </x-dropdown-link>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
            </div>

            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-brand-600 hover:bg-brand-50 focus:outline-none focus:bg-brand-50 focus:text-brand-600 transition duration-150 ease-in-out dark:text-stone-400 dark:hover:text-white dark:hover:bg-stone-700 dark:focus:bg-stone-700">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                Dashboard
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.appointments.index')" :active="request()->routeIs('admin.appointments.*')">
                Agenda
            </x-responsive-nav-link>
            <div class="pt-2 pb-1">
                <div class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider dark:text-stone-500">Cadastros</div>
                <x-responsive-nav-link :href="route('admin.customers.index')" :active="request()->routeIs('admin.customers.*')">
                    Clientes
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.services.index')" :active="request()->routeIs('admin.services.*')">
                    Serviços
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.products.index')" :active="request()->routeIs('admin.products.*') && !request()->routeIs('admin.products.stock-report')">
                    Produtos
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.products.stock-report')" :active="request()->routeIs('admin.products.stock-report')">
                    Relatório de Estoque
                </x-responsive-nav-link>
            </div>
            <div class="pt-2 pb-1">
                <div class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider dark:text-stone-500">Financeiros</div>
                <x-responsive-nav-link :href="route('admin.financial.index')" :active="request()->routeIs('admin.financial.*')">
                    Financeiro
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.commissions.index')" :active="request()->routeIs('admin.commissions.*')">
                    Comissões
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.sales.index')" :active="request()->routeIs('admin.sales.*')">
                    Vendas
                </x-responsive-nav-link>
            </div>
            <x-responsive-nav-link :href="route('admin.settings.working-hours')" :active="request()->routeIs('admin.settings.*') && !request()->routeIs('admin.settings.whatsapp') && !request()->routeIs('admin.settings.evolution') && !request()->routeIs('admin.settings.bot')">
                Horários
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.loyalty.index')" :active="request()->routeIs('admin.loyalty.*')">
                Fidelidade
            </x-responsive-nav-link>
            <div class="pt-2 pb-1">
                <div class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider dark:text-stone-500">WhatsApp</div>
                <x-responsive-nav-link :href="route('admin.settings.evolution')" :active="request()->routeIs('admin.settings.evolution')">
                    Evolution API
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.settings.bot')" :active="request()->routeIs('admin.settings.bot')">
                    Configurar Bot
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.bot-messages.index')" :active="request()->routeIs('admin.bot-messages.*')">
                    Mensagens do Bot
                </x-responsive-nav-link>
            </div>
        </div>

            <div class="pt-4 pb-1 border-t border-gray-200 dark:border-stone-700">
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800 dark:text-stone-200">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500 dark:text-stone-400">{{ Auth::user()->email }}</div>
                </div>
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('admin.settings.company')" :active="request()->routeIs('admin.settings.company')">
                        Empresa
                    </x-responsive-nav-link>
                    <x-responsive-nav-link href="#" @click.prevent="dark = !dark">
                        Tema: <span x-text="dark ? 'Claro' : 'Escuro'"></span>
                    </x-responsive-nav-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
    </div>
</nav>
