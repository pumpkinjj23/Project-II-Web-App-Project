/**
 * app.js - Northwind BMS Interactive Application Controller
 * รวม Hero AI Landing + 12-Screen Management Suite ตามข้อกำหนด
 */

const App = {
    currentScreen: 'landing', // เริ่มต้นที่หน้า Landing หรือ Dashboard
    editProductId: null,
    categoriesCache: [],
    suppliersCache: [],

    init: function() {
        this.generateLogoRays();
        this.bindEvents();
        this.setupEntranceAnimations();
        this.checkAuthStatus();
        this.loadCommonData();
        this.showScreen(this.currentScreen);
    },

    // 1. สร้าง Logo Rays 24 เส้น
    generateLogoRays: function() {
        document.querySelectorAll('.logo-svg .rays').forEach(raysGroup => {
            const cx = 26, cy = 26, r1 = 10.4, r2 = 22.6, count = 24;
            let lines = '';
            for (let i = 0; i < count; i++) {
                const angle = -Math.PI / 2 + (i * 2 * Math.PI / count);
                const x1 = (cx + r1 * Math.cos(angle)).toFixed(2);
                const y1 = (cy + r1 * Math.sin(angle)).toFixed(2);
                const x2 = (cx + r2 * Math.cos(angle)).toFixed(2);
                const y2 = (cy + r2 * Math.sin(angle)).toFixed(2);
                lines += `<line x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}" />`;
            }
            raysGroup.innerHTML = lines;
        });
    },

    // 2. ควบคุมการสลับหน้าจอ (Landing vs 12 Management Screens)
    showScreen: function(screenId) {
        this.currentScreen = screenId;

        // ปิด Mobile Overlay ถ้าเปิดอยู่
        const overlay = document.getElementById('navOverlay');
        const burger = document.getElementById('burgerBtn');
        if (overlay && overlay.classList.contains('open')) {
            overlay.classList.remove('open');
            document.body.classList.remove('menu-open');
            if (burger) burger.setAttribute('aria-expanded', 'false');
        }

        // ซ่อนทุก Screen View
        document.querySelectorAll('.screen-view').forEach(s => s.classList.remove('active'));

        // ตรวจสอบว่าเป็น Landing Showcase หรือ Management Suite
        const isLanding = (screenId === 'landing');
        const isLogin = (screenId === 'login');

        const landingContainer = document.getElementById('view-landing-wrapper');
        const bmsAppShell = document.getElementById('view-bms-app-shell');

        if (landingContainer && bmsAppShell) {
            if (isLanding) {
                landingContainer.style.display = 'block';
                bmsAppShell.style.display = 'none';
            } else {
                landingContainer.style.display = 'none';
                bmsAppShell.style.display = 'flex';

                // ซ่อน Sidebar / Topbar ถ้าอยู่หน้า Login
                const sidebar = document.querySelector('.app-sidebar');
                const topbar = document.querySelector('.bms-topbar');
                const main = document.querySelector('.app-main');

                if (sidebar && topbar && main) {
                    if (isLogin) {
                        sidebar.style.display = 'none';
                        topbar.style.display = 'none';
                        main.style.marginLeft = '0';
                    } else {
                        sidebar.style.display = 'flex';
                        topbar.style.display = 'flex';
                        main.style.marginLeft = '';
                    }
                }

                // แสดงหน้าจอย่อยใน BMS Suite
                const targetScreen = document.getElementById(`screen-${screenId}`);
                if (targetScreen) {
                    targetScreen.classList.add('active');
                }
            }
        }

        // ปิด Screen Navigator ถ้าเปิดอยู่
        this.closeScreenNavigator();

        // อัปเดตสถานะปุ่มใน Header Nav Bar
        document.querySelectorAll('.header-nav-link').forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-screen') === screenId);
        });

        // อัปเดตสถานะปุ่มใน Floating Bottom Dock
        document.querySelectorAll('.dock-pill').forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-screen') === screenId);
        });

        // อัปเดตสถานะใน Screen Navigator Modal
        document.querySelectorAll('.nav-screen-item').forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-screen') === screenId);
        });

        // อัปเดตสถานะปุ่มใน Switcher Bar ด้านบน (ถ้ามี)
        document.querySelectorAll('.screen-tab-btn').forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-screen') === screenId);
        });

        // อัปเดต Sidebar Active Item
        document.querySelectorAll('.sidebar-nav-item').forEach(item => {
            item.classList.toggle('active', item.getAttribute('data-screen') === screenId);
        });

        // อัปเดต Breadcrumb
        this.updateBreadcrumb(screenId);

        // โหลดข้อมูลเฉพาะของแต่ละหน้าจอ
        switch(screenId) {
            case 'dashboard':
                this.loadDashboard();
                break;
            case 'products':
                this.loadProducts();
                break;
            case 'product-form':
                this.setupProductForm();
                break;
            case 'categories':
                this.loadCategories();
                break;
            case 'orders':
                this.loadOrders();
                break;
            case 'order-detail':
                this.loadOrderDetail(10443);
                break;
            case 'customers':
                this.loadCustomers();
                break;
            case 'suppliers':
                this.loadSuppliers();
                break;
            case 'employees':
                this.loadEmployees();
                break;
            case 'shippers':
                this.loadShippers();
                break;
            case 'reports':
                this.loadReports();
                break;
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
    },

    updateBreadcrumb: function(screenId) {
        const titles = {
            'landing': 'หน้าแรกภาพรวม',
            'dashboard': 'แดชบอร์ดภาพรวม',
            'products': 'รายการสินค้า (tb_products)',
            'product-form': 'เพิ่ม/แก้ไขข้อมูลสินค้า',
            'categories': 'หมวดหมู่สินค้า (tb_categories)',
            'orders': 'คำสั่งซื้อ (tb_orders)',
            'order-detail': 'รายละเอียดคำสั่งซื้อ (Order Details)',
            'customers': 'ทะเบียนลูกค้า (tb_customers)',
            'suppliers': 'ผู้จัดจำหน่าย (tb_suppliers)',
            'employees': 'รายชื่อพนักงาน (tb_employees)',
            'shippers': 'บริษัทขนส่ง (tb_shippers)',
            'reports': 'รายงานและวิเคราะห์ข้อมูล'
        };
        const el = document.getElementById('bms-breadcrumb-cur');
        if (el && titles[screenId]) {
            el.textContent = titles[screenId];
        }
    },

    openScreenNavigator: function() {
        const modal = document.getElementById('screenNavigatorOverlay');
        if (modal) modal.classList.add('open');
    },

    closeScreenNavigator: function() {
        const modal = document.getElementById('screenNavigatorOverlay');
        if (modal) modal.classList.remove('open');
    },

    toggleDock: function() {
        const dock = document.getElementById('floatingDock');
        if (dock) dock.classList.toggle('minimized');
    },

    quickSearch: function(val) {
        if (!val) return;
        const s = this.currentScreen;
        if (s === 'products') {
            const inp = document.getElementById('product-search-input');
            if (inp) { inp.value = val; this.loadProducts(); }
        } else if (s === 'orders') {
            const inp = document.getElementById('order-search-input');
            if (inp) { inp.value = val; this.loadOrders(); }
        } else if (s === 'customers') {
            const inp = document.getElementById('cust-search-input');
            if (inp) { inp.value = val; this.loadCustomers(); }
        } else if (s === 'suppliers') {
            const inp = document.getElementById('sup-search-input');
            if (inp) { inp.value = val; this.loadSuppliers(); }
        } else {
            this.showScreen('products');
            setTimeout(() => {
                const inp = document.getElementById('product-search-input');
                if (inp) { inp.value = val; this.loadProducts(); }
            }, 100);
        }
    },

    bindEvents: function() {
        // Global Keyboard Handler (Escape closes modal)
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                App.closeScreenNavigator();
            }
        });

        // Prototype Tab Switcher Buttons
        document.querySelectorAll('.screen-tab-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const s = btn.getAttribute('data-screen');
                if (s) App.showScreen(s);
            });
        });

        // Sidebar Navigation
        document.querySelectorAll('.sidebar-nav-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const s = item.getAttribute('data-screen');
                if (s) App.showScreen(s);
            });
        });

        // Mobile Burger
        const burger = document.getElementById('burgerBtn');
        const overlay = document.getElementById('navOverlay');
        if (burger && overlay) {
            burger.addEventListener('click', () => {
                const isOpen = burger.getAttribute('aria-expanded') === 'true';
                if (isOpen) {
                    burger.setAttribute('aria-expanded', 'false');
                    overlay.classList.remove('open');
                    document.body.classList.remove('menu-open');
                } else {
                    burger.setAttribute('aria-expanded', 'true');
                    overlay.classList.add('open');
                    document.body.classList.add('menu-open');
                }
            });

            document.querySelectorAll('.overlay-link, .overlay-cta-btn').forEach(l => {
                l.addEventListener('click', (e) => {
                    const scr = l.getAttribute('data-screen');
                    if (scr) {
                        e.preventDefault();
                        App.showScreen(scr);
                    }
                });
            });
        }

        // Hero Composer Form Submit
        const composerInput = document.getElementById('hero-composer-input');
        const composerSendBtn = document.getElementById('hero-composer-send');

        if (composerSendBtn && composerInput) {
            const handleComposerQuery = () => {
                const q = composerInput.value.trim();
                if (q) App.processComposerQuery(q);
            };

            composerSendBtn.addEventListener('click', handleComposerQuery);
            composerInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    handleComposerQuery();
                }
            });
        }

        // Login Form Submit
        const loginForm = document.getElementById('login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => {
                e.preventDefault();
                App.handleLogin();
            });
        }
    },

    // 3. จัดการคำถามใน Hero AI Composer
    setComposerQuery: function(text) {
        const input = document.getElementById('hero-composer-input');
        if (input) {
            input.value = text;
            this.processComposerQuery(text);
        }
    },

    processComposerQuery: async function(query) {
        Swal.fire({
            title: '🧠 กำลังประมวลผลข้อคำถาม...',
            html: `<div class="text-secondary small mt-2">วิเคราะห์โครงสร้างข้อมูล Northwind Database สำหรับ: <strong>"${query}"</strong></div>`,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // ค้นหาคีย์เวิร์ด
        const qLower = query.toLowerCase();

        setTimeout(async () => {
            if (qLower.includes('สินค้า') || qLower.includes('ชาไทย') || qLower.includes('product') || qLower.includes('10,000') || qLower.includes('ราคา')) {
                const res = await API.get('/products', { search: qLower.includes('ชาไทย') ? 'ชาไทย' : '' });
                const count = res.total || (res.data ? res.data.length : 79);
                Swal.fire({
                    icon: 'success',
                    title: 'ผลการวิเคราะห์ข้อมูลสินค้า',
                    html: `
                        <div class="text-start">
                            <p>🔍 ตรวจพบข้อมูลสินค้าที่เกี่ยวข้องทั้งหมด <strong>${count} รายการ</strong> ในฐานข้อมูล <code>tb_products</code></p>
                            <ul>
                                <li><strong>สินค้าเด่น:</strong> ชาไทย (Chai) ฿18.00 (เครื่องดื่ม)</li>
                                <li><strong>สินค้าตรวจสอบ:</strong> NIKE รองเท้า (฿25,000.00 - ราคาสูงกว่า ฿10,000)</li>
                            </ul>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: '📦 ไปยังหน้ารายการสินค้า',
                    cancelButtonText: 'ปิด'
                }).then(result => {
                    if (result.isConfirmed) App.showScreen('products');
                });
            } else if (qLower.includes('ยุโรป') || qLower.includes('ลูกค้า') || qLower.includes('customer') || qLower.includes('ernst')) {
                Swal.fire({
                    icon: 'success',
                    title: 'ผลการวิเคราะห์ตลาดและลูกค้าในยุโรป',
                    html: `
                        <div class="text-start">
                            <p>🌐 ฐานข้อมูล <code>tb_customers</code> มีลูกค้าในยุโรปเป็นสัดส่วน <strong>52%</strong>:</p>
                            <ul>
                                <li><strong>ลูกค้ายอดสั่งซื้อสูงสุด:</strong> Ernst Handel (Graz, ออสเตรีย) - 10 คำสั่งซื้อ</li>
                                <li><strong>การขนส่ง:</strong> ดำเนินการผ่าน Federal Shipping และ United Package</li>
                            </ul>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: '👥 ไปยังทะเบียนลูกค้า',
                    cancelButtonText: 'ปิด'
                }).then(result => {
                    if (result.isConfirmed) App.showScreen('customers');
                });
            } else if (qLower.includes('คำสั่งซื้อ') || qLower.includes('order') || qLower.includes('ยอดขาย')) {
                Swal.fire({
                    icon: 'success',
                    title: 'ผลการวิเคราะห์คำสั่งซื้อ Northwind',
                    html: `
                        <div class="text-start">
                            <p>🛒 ข้อมูลจาก <code>tb_orders</code> และ <code>tb_orderdetails</code>:</p>
                            <ul>
                                <li><strong>คำสั่งซื้อทั้งหมด:</strong> 196 รายการ</li>
                                <li><strong>สถานะส่วนใหญ่:</strong> จัดส่งแล้ว (Speedy Express, United Package, Federal Shipping)</li>
                            </ul>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: '🛒 ไปยังหน้าคำสั่งซื้อ',
                    cancelButtonText: 'ปิด'
                }).then(result => {
                    if (result.isConfirmed) App.showScreen('orders');
                });
            } else {
                Swal.fire({
                    icon: 'info',
                    title: 'ระบบประมวลผลการตัดสินใจ',
                    html: `
                        <div class="text-start">
                            <p>ระบบได้จำแนกข้อมูลตามข้อสอบถาม <strong>"${query}"</strong> เรียบร้อยแล้ว</p>
                            <p>สามารถดูข้อมูลเชิงลึกได้ผ่านระบบจัดการทั้ง 12 หน้าจอ</p>
                        </div>
                    `,
                    confirmButtonText: '📊 เปิดแดชบอร์ด'
                }).then(() => {
                    App.showScreen('dashboard');
                });
            }
        }, 600);
    },

    // 4. แอนิเมชันเปิดหน้าจอ (WAAPI)
    setupEntranceAnimations: function() {
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (prefersReducedMotion) {
            document.documentElement.classList.remove('entrance', 'entrance-s2');
            document.documentElement.classList.add('hero-ready');
            return;
        }

        const REVEAL = 'cubic-bezier(0.16, 1, 0.3, 1)';
        const LIFT = 'cubic-bezier(0.22, 1, 0.36, 1)';
        const D = window.innerWidth <= 680 ? 0.7 : 1.0;

        // Hero Master Timeline
        const playHero = () => {
            const logo = document.querySelector('.logo-link');
            const navLinks = document.querySelectorAll('.nav-links li');
            const burger = document.querySelector('.burger');
            const startFree = document.querySelector('.btn-start-free');
            const hlLines = document.querySelectorAll('.section-one .hl-line');
            const subtitle = document.querySelector('.subtitle');
            const composerShell = document.querySelector('.composer-shell');
            const controls = document.querySelectorAll('.composer-control');
            const glow = document.querySelector('.composer-glow');
            const brands = document.querySelectorAll('.brand-item');

            const anims = [];

            if (logo) anims.push(logo.animate([{ opacity: 0, transform: 'scale(0.92)' }, { opacity: 1, transform: 'scale(1)' }], { duration: 600, easing: LIFT, fill: 'forwards' }));

            navLinks.forEach((item, idx) => {
                anims.push(item.animate([{ opacity: 0, transform: `translateY(${10 * D}px)` }, { opacity: 1, transform: 'translateY(0)' }], { duration: 500, delay: 100 + idx * 50, easing: LIFT, fill: 'forwards' }));
            });

            if (burger) anims.push(burger.animate([{ opacity: 0, transform: `translateY(${10 * D}px)` }, { opacity: 1, transform: 'translateY(0)' }], { duration: 500, delay: 160, easing: LIFT, fill: 'forwards' }));
            if (startFree) anims.push(startFree.animate([{ opacity: 0, transform: `translateY(${10 * D}px)` }, { opacity: 1, transform: 'translateY(0)' }], { duration: 500, delay: 260, easing: LIFT, fill: 'forwards' }));

            hlLines.forEach((line, idx) => {
                anims.push(line.animate([
                    { opacity: 0, transform: 'translateY(108%)', offset: 0 },
                    { opacity: 1, transform: 'translateY(85%)', offset: 0.14 },
                    { opacity: 1, transform: 'translateY(0)', offset: 1 }
                ], { duration: 950, delay: 200 + idx * 90, easing: REVEAL, fill: 'forwards' }));
            });

            if (subtitle) anims.push(subtitle.animate([{ opacity: 0, transform: `translateY(${14 * D}px)` }, { opacity: 1, transform: 'translateY(0)' }], { duration: 650, delay: 520, easing: LIFT, fill: 'forwards' }));

            if (composerShell) anims.push(composerShell.animate([{ opacity: 0, transform: `translate3d(0, ${22 * D}px, 0) scale(0.985)` }, { opacity: 1, transform: 'translate3d(0, 0, 0) scale(1)' }], { duration: 950, delay: 640, easing: REVEAL, fill: 'forwards' }));

            controls.forEach((ctrl, idx) => {
                anims.push(ctrl.animate([{ opacity: 0, transform: `translateY(${10 * D}px)` }, { opacity: 1, transform: 'translateY(0)' }], { duration: 500, delay: 840 + idx * 55, easing: LIFT, fill: 'forwards' }));
            });

            if (glow) anims.push(glow.animate([{ clipPath: 'inset(0 40% 0 40%)', opacity: 0 }, { clipPath: 'inset(0 0% 0 0%)', opacity: 0.95 }], { duration: 800, delay: 940, easing: REVEAL, fill: 'forwards' }));

            brands.forEach((brand, idx) => {
                anims.push(brand.animate([{ opacity: 0, transform: `translateY(${12 * D}px)` }, { opacity: 0.85, transform: 'translateY(0)' }], { duration: 550, delay: 1080 + idx * 60, easing: LIFT, fill: 'forwards' }));
            });

            setTimeout(() => {
                document.documentElement.classList.remove('entrance');
                anims.forEach(a => { try { a.cancel(); } catch(e){} });
                document.documentElement.classList.add('hero-ready');
            }, 1800);
        };

        if (document.fonts && document.fonts.ready) {
            Promise.race([
                document.fonts.ready,
                new Promise(res => setTimeout(res, 400))
            ]).then(playHero);
        } else {
            setTimeout(playHero, 400);
        }

        // Section 2 Observer
        const s2 = document.querySelector('.section-two');
        if (s2 && 'IntersectionObserver' in window) {
            const obs = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.playSection2Entrance();
                        obs.disconnect();
                    }
                });
            }, { rootMargin: '0px 0px -20% 0px', threshold: 0 });
            obs.observe(s2);
        }
    },

    playSection2Entrance: function() {
        const REVEAL = 'cubic-bezier(0.16, 1, 0.3, 1)';
        const LIFT = 'cubic-bezier(0.22, 1, 0.36, 1)';
        const D = window.innerWidth <= 680 ? 0.7 : 1.0;

        const s2Lines = document.querySelectorAll('.section-two .hl-line');
        const s2Subhead = document.querySelector('.s2-subhead');
        const panel = document.querySelector('.section-two .panel');
        const liveReasoning = document.querySelector('.live-reasoning-row');
        const chatCard = document.querySelector('.chat-card');
        const chatMsgs = document.querySelectorAll('.chat-msg');
        const chatInput = document.querySelector('.chat-input-bar');

        const s2Anims = [];

        s2Lines.forEach((line, idx) => {
            s2Anims.push(line.animate([
                { opacity: 0, transform: 'translateY(108%)', offset: 0 },
                { opacity: 1, transform: 'translateY(85%)', offset: 0.14 },
                { opacity: 1, transform: 'translateY(0)', offset: 1 }
            ], { duration: 950, delay: idx * 90, easing: REVEAL, fill: 'forwards' }));
        });

        if (s2Subhead) s2Anims.push(s2Subhead.animate([{ opacity: 0, transform: `translateY(${14 * D}px)` }, { opacity: 1, transform: 'translateY(0)' }], { duration: 650, delay: 300, easing: LIFT, fill: 'forwards' }));

        if (panel) s2Anims.push(panel.animate([{ opacity: 0, transform: `translate3d(0, ${26 * D}px, 0) scale(0.985)` }, { opacity: 1, transform: 'translate3d(0, 0, 0) scale(1)' }], { duration: 1000, delay: 400, easing: REVEAL, fill: 'forwards' }));

        if (liveReasoning) s2Anims.push(liveReasoning.animate([{ opacity: 0, transform: `translateY(${10 * D}px)` }, { opacity: 1, transform: 'translateY(0)' }], { duration: 550, delay: 680, easing: LIFT, fill: 'forwards' }));

        if (chatCard) s2Anims.push(chatCard.animate([{ transform: `translateY(${18 * D}px)` }, { transform: 'translateY(0)' }], { duration: 850, delay: 720, easing: REVEAL, fill: 'forwards' }));

        chatMsgs.forEach((msg, idx) => {
            s2Anims.push(msg.animate([{ opacity: 0, transform: `translateY(${10 * D}px)` }, { opacity: 1, transform: 'translateY(0)' }], { duration: 500, delay: 880 + idx * 65, easing: LIFT, fill: 'forwards' }));
        });

        if (chatInput) s2Anims.push(chatInput.animate([{ opacity: 0, transform: `translateY(${10 * D}px)` }, { opacity: 1, transform: 'translateY(0)' }], { duration: 500, delay: 1240, easing: LIFT, fill: 'forwards' }));

        setTimeout(() => {
            document.documentElement.classList.remove('entrance-s2');
            s2Anims.forEach(a => { try { a.cancel(); } catch(e){} });
        }, 1800);
    },

    checkAuthStatus: function() {
        const user = API.getUser();
        if (user) {
            document.querySelectorAll('.user-display-name').forEach(el => el.textContent = user.name || 'วชิรา ล.');
            document.querySelectorAll('.user-display-role').forEach(el => el.textContent = user.role || 'ผู้ดูแลระบบ');
        }
    },

    loadCommonData: async function() {
        try {
            const [catRes, supRes] = await Promise.all([
                API.get('/categories'),
                API.get('/suppliers')
            ]);
            if (catRes && catRes.data) this.categoriesCache = catRes.data;
            if (supRes && supRes.data) this.suppliersCache = supRes.data;
        } catch (e) {
            console.warn("loadCommonData error:", e);
        }
    },

    // ==========================================
    // 01: Login Handle
    // ==========================================
    handleLogin: async function() {
        const email = document.getElementById('login-email').value;
        const password = document.getElementById('login-password').value;

        if (!email || !password) {
            Swal.fire({ icon: 'warning', title: 'กรุณากรอกข้อมูล', text: 'กรุณากรอกอีเมลและรหัสผ่าน' });
            return;
        }

        const res = await API.post('/auth/login', { email, password });
        if (res && res.status) {
            API.setToken(res.data.token);
            API.setUser(res.data.user);
            this.checkAuthStatus();

            Swal.fire({
                icon: 'success',
                title: 'เข้าสู่ระบบสำเร็จ',
                text: `ยินดีต้อนรับ ${res.data.user.name}`,
                timer: 1500,
                showConfirmButton: false
            });

            setTimeout(() => {
                App.showScreen('dashboard');
            }, 1000);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'เข้าสู่ระบบไม่สำเร็จ',
                text: res.message || 'อีเมลหรือรหัสผ่านไม่ถูกต้อง'
            });
        }
    },

    logout: function() {
        Swal.fire({
            title: 'ยืนยันการออกจากระบบ?',
            text: 'คุณต้องการออกจากระบบหรือไม่',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'ออกจากระบบ',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                API.removeToken();
                App.showScreen('landing');
            }
        });
    },

    // ==========================================
    // 02: Dashboard
    // ==========================================
    loadDashboard: async function() {
        const res = await API.get('/dashboard/stats');
        if (!res || !res.status) return;

        const d = res.data;
        document.getElementById('dash-kpi-orders').textContent = d.kpis.total_orders;
        document.getElementById('dash-kpi-products').textContent = d.kpis.total_products;
        document.getElementById('dash-kpi-customers').textContent = d.kpis.total_customers;
        document.getElementById('dash-kpi-suppliers').textContent = d.kpis.total_suppliers;

        const tbody = document.getElementById('dash-recent-orders-tbody');
        if (tbody && d.recent_orders) {
            tbody.innerHTML = d.recent_orders.map(o => `
                <tr>
                    <td style="font-family:'IBM Plex Mono';font-weight:600">#${o.order_id}</td>
                    <td>${o.customer}</td>
                    <td>${o.employee}</td>
                    <td>${o.date}</td>
                    <td>${o.shipper}</td>
                    <td><span class="badge-bms ${o.badge_class}">${o.status}</span></td>
                </tr>
            `).join('');
        }
    },

    // ==========================================
    // 03: Products List
    // ==========================================
    loadProducts: async function(page = 1) {
        const search = document.getElementById('prod-search-input')?.value || '';
        const catId = document.getElementById('prod-filter-cat')?.value || '';
        const supId = document.getElementById('prod-filter-sup')?.value || '';

        this.populateProductFilters();

        const res = await API.get('/products', {
            search: search,
            category_id: catId,
            supplier_id: supId,
            page: page,
            limit: 25
        });

        if (!res || !res.status) return;

        const products = res.data || [];
        const total = res.total || products.length;

        const totalEl = document.getElementById('prod-total-count');
        if (totalEl) totalEl.textContent = `${total} รายการ`;

        const tbody = document.getElementById('products-table-tbody');
        if (tbody) {
            if (products.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-muted">ไม่พบข้อมูลสินค้าที่ตรงกับเงื่อนไข</td></tr>`;
            } else {
                tbody.innerHTML = products.map(p => `
                    <tr>
                        <td style="font-family:'IBM Plex Mono'">${String(p.i_ProductID).padStart(3, '0')}</td>
                        <td style="font-weight:600">${p.c_ProductName}</td>
                        <td><span class="badge-bms badge-blue">${p.c_CategoryName || 'เครื่องดื่ม'}</span></td>
                        <td>${p.c_SupplierName || '—'}</td>
                        <td>${p.c_Unit || '—'}</td>
                        <td style="font-weight:700;font-family:'IBM Plex Mono';${p.i_Price > 10000 ? 'color:var(--danger)' : ''}">฿${parseFloat(p.i_Price).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                        <td><span class="badge-bms ${p.badge_class || 'badge-green'}">${p.status || 'ใช้งานอยู่'}</span></td>
                        <td>
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-outline-light" onclick="App.editProduct(${p.i_ProductID})" title="แก้ไข">✏️</button>
                                <button class="btn btn-sm btn-outline-danger" onclick="App.deleteProduct(${p.i_ProductID}, '${p.c_ProductName.replace(/'/g, "\\'")}')" title="ลบ">🗑️</button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            }
        }

        this.renderPagination('prod-pagination', total, page, 25, (p) => App.loadProducts(p));
    },

    populateProductFilters: function() {
        const catSelect = document.getElementById('prod-filter-cat');
        if (catSelect && catSelect.options.length <= 1 && this.categoriesCache.length > 0) {
            this.categoriesCache.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.i_CategoryID;
                opt.textContent = c.c_CategoryName;
                catSelect.appendChild(opt);
            });
        }
        const supSelect = document.getElementById('prod-filter-sup');
        if (supSelect && supSelect.options.length <= 1 && this.suppliersCache.length > 0) {
            this.suppliersCache.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.i_SupplierID;
                opt.textContent = s.c_SupplierName;
                supSelect.appendChild(opt);
            });
        }
    },

    // ==========================================
    // 04: Product Form
    // ==========================================
    setupProductForm: async function(productId = null) {
        this.editProductId = productId;
        const formTitle = document.getElementById('product-form-title');
        const formDesc = document.getElementById('product-form-desc');
        const submitBtn = document.getElementById('product-form-submit-btn');

        const catSelect = document.getElementById('pform-category');
        const supSelect = document.getElementById('pform-supplier');

        if (catSelect && this.categoriesCache.length > 0) {
            catSelect.innerHTML = '<option value="">— เลือกหมวดหมู่ —</option>' +
                this.categoriesCache.map(c => `<option value="${c.i_CategoryID}">${c.c_CategoryName}</option>`).join('');
        }
        if (supSelect && this.suppliersCache.length > 0) {
            supSelect.innerHTML = '<option value="">— เลือกผู้จัดจำหน่าย —</option>' +
                this.suppliersCache.map(s => `<option value="${s.i_SupplierID}">${s.c_SupplierName}</option>`).join('');
        }

        if (productId) {
            formTitle.textContent = `แก้ไขข้อมูลสินค้า (รหัส #${productId})`;
            formDesc.textContent = 'ปรับปรุงรายละเอียดข้อมูลสินค้าในระบบ';
            submitBtn.innerHTML = '💾 บันทึกการแก้ไข';

            const res = await API.get(`/products/${productId}`);
            if (res && res.status && res.data) {
                const p = res.data;
                document.getElementById('pform-name').value = p.c_ProductName || '';
                document.getElementById('pform-category').value = p.i_CategoryID || '';
                document.getElementById('pform-supplier').value = p.i_SupplierID || '';
                document.getElementById('pform-unit').value = p.c_Unit || '';
                document.getElementById('pform-price').value = p.i_Price || '';
                document.getElementById('pform-status').value = p.status || 'ใช้งานอยู่';
            }
        } else {
            formTitle.textContent = 'เพิ่มสินค้าใหม่';
            formDesc.textContent = 'กรอกข้อมูลสินค้าให้ครบถ้วน (ช่องที่มี * กำหนดต้องกรอก)';
            submitBtn.innerHTML = '💾 บันทึกสินค้า';
            document.getElementById('product-edit-form').reset();
        }
    },

    editProduct: function(id) {
        this.setupProductForm(id);
        this.showScreen('product-form');
    },

    saveProduct: async function() {
        const name = document.getElementById('pform-name').value.trim();
        const catId = document.getElementById('pform-category').value;
        const supId = document.getElementById('pform-supplier').value;
        const unit = document.getElementById('pform-unit').value.trim();
        const price = parseFloat(document.getElementById('pform-price').value);

        if (!name) { Swal.fire('ข้อผิดพลาด', 'กรุณาระบุชื่อสินค้า', 'warning'); return; }
        if (name.length > 30) { Swal.fire('ข้อผิดพลาด', 'ชื่อสินค้าต้องมีความยาวไม่เกิน 30 ตัวอักษร', 'warning'); return; }
        if (!catId) { Swal.fire('ข้อผิดพลาด', 'กรุณาเลือกหมวดหมู่สินค้า', 'warning'); return; }
        if (!supId) { Swal.fire('ข้อผิดพลาด', 'กรุณาเลือกผู้จัดจำหน่าย', 'warning'); return; }
        if (!unit) { Swal.fire('ข้อผิดพลาด', 'กรุณาระบุหน่วยบรรจุภัณฑ์', 'warning'); return; }
        if (isNaN(price) || price <= 0) { Swal.fire('ข้อผิดพลาด', 'ราคาต่อหน่วยต้องมากกว่า 0 บาท', 'warning'); return; }

        const payload = {
            c_ProductName: name,
            i_CategoryID: parseInt(catId),
            i_SupplierID: parseInt(supId),
            c_Unit: unit,
            i_Price: price
        };

        let res = this.editProductId ? await API.put(`/products/${this.editProductId}`, payload) : await API.post('/products', payload);

        if (res && res.status) {
            Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message || 'บันทึกข้อมูลสินค้าเรียบร้อยแล้ว', timer: 1500, showConfirmButton: false });
            setTimeout(() => { App.showScreen('products'); }, 1000);
        } else {
            Swal.fire('เกิดข้อผิดพลาด', res.message || 'ไม่สามารถบันทึกข้อมูลได้', 'error');
        }
    },

    deleteProduct: function(id, name) {
        Swal.fire({
            title: `ยืนยันการลบสินค้า #${id}?`,
            text: `ต้องการลบรายการ "${name}" ออกจากระบบหรือไม่`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            confirmButtonText: '🗑️ ยืนยันการลบ',
            cancelButtonText: 'ยกเลิก'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const res = await API.delete(`/products/${id}`);
                if (res && res.status) {
                    Swal.fire('ลบสำเร็จ', res.message || 'ลบข้อมูลสินค้าเรียบร้อยแล้ว', 'success');
                    App.loadProducts();
                } else {
                    Swal.fire('ไม่สามารถลบได้', res.message || 'เกิดข้อผิดพลาดในการลบ', 'error');
                }
            }
        });
    },

    // ==========================================
    // 05: Categories
    // ==========================================
    loadCategories: async function() {
        const res = await API.get('/categories');
        if (!res || !res.status) return;

        const cats = res.data || [];
        const countEl = document.getElementById('cat-total-count');
        if (countEl) countEl.textContent = `${cats.length} หมวดหมู่`;

        const grid = document.getElementById('categories-grid');
        if (grid) {
            grid.innerHTML = cats.map(c => `
                <div class="col">
                    <div class="cat-card">
                        <div>
                            <div class="cat-icon">${c.icon || '📦'}</div>
                            <div class="cat-name">${c.c_CategoryName}</div>
                            <div class="cat-desc">${c.c_Description || '—'}</div>
                            <div class="cat-count" style="${c.product_count == 1 ? 'color:var(--warn)' : ''}">
                                ${c.product_count || 0} รายการสินค้า
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline-light" onclick="App.editCategory(${c.i_CategoryID}, '${c.c_CategoryName}', '${c.c_Description}')">✏️</button>
                    </div>
                </div>
            `).join('');
        }
    },

    editCategory: function(id, name, desc) {
        Swal.fire({
            title: `แก้ไขหมวดหมู่ #${id}`,
            html: `
                <div class="text-start mb-2"><label class="form-label small fw-bold">ชื่อหมวดหมู่:</label><input id="swal-cat-name" class="form-control" value="${name}"></div>
                <div class="text-start"><label class="form-label small fw-bold">คำอธิบาย:</label><input id="swal-cat-desc" class="form-control" value="${desc}"></div>
            `,
            showCancelButton: true,
            confirmButtonText: '💾 บันทึก',
            cancelButtonText: 'ยกเลิก',
            preConfirm: () => {
                const newName = document.getElementById('swal-cat-name').value.trim();
                const newDesc = document.getElementById('swal-cat-desc').value.trim();
                if (!newName) { Swal.showValidationMessage('กรุณาระบุชื่อหมวดหมู่'); return false; }
                return { c_CategoryName: newName, c_Description: newDesc };
            }
        }).then(async (result) => {
            if (result.isConfirmed) {
                const res = await API.put(`/categories/${id}`, result.value);
                if (res && res.status) {
                    Swal.fire('สำเร็จ', 'แก้ไขหมวดหมู่สินค้าเรียบร้อยแล้ว', 'success');
                    App.loadCategories();
                    App.loadCommonData();
                }
            }
        });
    },

    addCategoryPrompt: function() {
        Swal.fire({
            title: 'เพิ่มหมวดหมู่สินค้าใหม่',
            html: `
                <div class="text-start mb-2"><label class="form-label small fw-bold">ชื่อหมวดหมู่:</label><input id="swal-cat-name" class="form-control" placeholder="เช่น อาหารแช่แข็ง"></div>
                <div class="text-start"><label class="form-label small fw-bold">คำอธิบาย:</label><input id="swal-cat-desc" class="form-control" placeholder="คำอธิบายหมวดหมู่"></div>
            `,
            showCancelButton: true,
            confirmButtonText: '➕ เพิ่มหมวดหมู่',
            cancelButtonText: 'ยกเลิก',
            preConfirm: () => {
                const name = document.getElementById('swal-cat-name').value.trim();
                const desc = document.getElementById('swal-cat-desc').value.trim();
                if (!name) { Swal.showValidationMessage('กรุณาระบุชื่อหมวดหมู่'); return false; }
                return { c_CategoryName: name, c_Description: desc };
            }
        }).then(async (result) => {
            if (result.isConfirmed) {
                const res = await API.post('/categories', result.value);
                if (res && res.status) {
                    Swal.fire('สำเร็จ', 'เพิ่มหมวดหมู่สินค้าใหม่เรียบร้อยแล้ว', 'success');
                    App.loadCategories();
                    App.loadCommonData();
                }
            }
        });
    },

    // ==========================================
    // 06: Orders List
    // ==========================================
    loadOrders: async function(page = 1) {
        const search = document.getElementById('order-search-input')?.value || '';
        const status = document.getElementById('order-filter-status')?.value || '';

        const res = await API.get('/orders', { search, status, page, limit: 25 });
        if (!res || !res.status) return;

        const orders = res.data || [];
        const total = res.total || orders.length;

        const countEl = document.getElementById('order-total-count');
        if (countEl) countEl.textContent = `${total} รายการ · เลขที่ #10248 – #10443`;

        const tbody = document.getElementById('orders-table-tbody');
        if (tbody) {
            tbody.innerHTML = orders.map(o => `
                <tr>
                    <td style="font-family:'IBM Plex Mono';font-weight:600">#${o.i_OrderID}</td>
                    <td style="font-weight:600">${o.customer_name || o.c_CustomerName || 'Ernst Handel'}</td>
                    <td>${o.employee_name || o.c_EmployeeName || 'แอนน์ ด็อดส์เวิร์ท'}</td>
                    <td>${o.c_OrderDate}</td>
                    <td>${o.shipper_name || o.c_ShipperName || 'Federal Shipping'}</td>
                    <td style="text-align:center">${o.items_count || (o.items ? o.items.length : 2)}</td>
                    <td><span class="badge-bms ${o.badge_class || 'badge-green'}">${o.status || 'จัดส่งแล้ว'}</span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-light" onclick="App.viewOrderDetail(${o.i_OrderID})" title="ดูรายละเอียด">👁</button>
                    </td>
                </tr>
            `).join('');
        }

        this.renderPagination('order-pagination', total, page, 25, (p) => App.loadOrders(p));
    },

    // ==========================================
    // 07: Order Detail
    // ==========================================
    viewOrderDetail: function(id) {
        this.loadOrderDetail(id);
        this.showScreen('order-detail');
    },

    loadOrderDetail: async function(id = 10443) {
        const res = await API.get(`/orders/${id}`);
        if (!res || !res.status || !res.data) return;

        const ord = res.data;
        document.getElementById('ord-detail-title-id').textContent = `#${ord.i_OrderID}`;
        document.getElementById('ord-detail-status-badge').textContent = `✅ ${ord.status || 'จัดส่งแล้ว'}`;
        document.getElementById('ord-detail-status-badge').className = `badge-bms ${ord.badge_class || 'badge-green'}`;

        document.getElementById('ord-detail-customer').textContent = ord.c_customername || ord.c_CustomerName || 'Ernst Handel';
        document.getElementById('ord-detail-contact').textContent = ord.c_contactname || 'Roland Mendel';
        document.getElementById('ord-detail-city').textContent = `${ord.c_city || 'Graz'}, ${ord.c_country || 'ออสเตรีย'}`;
        document.getElementById('ord-detail-postal').textContent = ord.c_postalcode || '8010';

        document.getElementById('ord-detail-date').textContent = ord.c_OrderDate || '2 ธ.ค. 2540';
        document.getElementById('ord-detail-shipper').textContent = ord.c_ShipperName || 'Federal Shipping';
        document.getElementById('ord-detail-employee').textContent = ord.employee_name || ord.c_EmployeeName || 'แอนน์ ด็อดส์เวิร์ท';

        const tbody = document.getElementById('ord-items-tbody');
        const items = ord.items || [];
        if (tbody) {
            tbody.innerHTML = items.map(it => `
                <tr>
                    <td style="font-weight:600">${it.c_ProductName}</td>
                    <td>${it.i_Quantity}</td>
                    <td>฿${parseFloat(it.i_Price).toFixed(2)}</td>
                    <td style="font-weight:700">฿${parseFloat(it.total || it.line_total || (it.i_Quantity * it.i_Price)).toFixed(2)}</td>
                </tr>
            `).join('');
        }

        document.getElementById('ord-summary-items-count').textContent = `${items.length} รายการ`;
        document.getElementById('ord-summary-units-count').textContent = `${ord.total_units || 20} หน่วย`;
        document.getElementById('ord-summary-grand-total').textContent = `฿${parseFloat(ord.grand_total || ord.total_amount || 368).toFixed(2)}`;
    },

    // ==========================================
    // 08: Customers
    // ==========================================
    loadCustomers: async function(page = 1) {
        const search = document.getElementById('cust-search-input')?.value || '';
        const country = document.getElementById('cust-filter-country')?.value || '';

        const res = await API.get('/customers', { search, country, page, limit: 25 });
        if (!res || !res.status) return;

        const custs = res.data || [];
        const total = res.total || custs.length;

        const countEl = document.getElementById('cust-total-count');
        if (countEl) countEl.textContent = `${total} ราย · 21 ประเทศ`;

        const tbody = document.getElementById('customers-table-tbody');
        if (tbody) {
            tbody.innerHTML = custs.map(c => `
                <tr>
                    <td style="font-family:'IBM Plex Mono'">${c.i_customerid}</td>
                    <td style="font-weight:600">${c.c_customername}</td>
                    <td>${c.c_contactname}</td>
                    <td>${c.c_city}</td>
                    <td>${c.country_display || c.c_country}</td>
                    <td style="font-family:'IBM Plex Mono'">${c.c_postalcode}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-light" onclick="App.viewCustomer(${c.i_customerid})">👁</button>
                    </td>
                </tr>
            `).join('');
        }

        this.renderPagination('cust-pagination', total, page, 25, (p) => App.loadCustomers(p));
    },

    viewCustomer: async function(id) {
        const res = await API.get(`/customers/${id}`);
        if (res && res.status && res.data) {
            const c = res.data;
            Swal.fire({
                title: `ข้อมูลลูกค้า #${c.i_customerid}`,
                html: `
                    <div class="text-start">
                        <p><strong>บริษัท:</strong> ${c.c_customername}</p>
                        <p><strong>ผู้ติดต่อ:</strong> ${c.c_contactname}</p>
                        <p><strong>ที่อยู่:</strong> ${c.c_address || '—'}</p>
                        <p><strong>เมือง / ประเทศ:</strong> ${c.c_city}, ${c.c_country}</p>
                        <p><strong>รหัสไปรษณีย์:</strong> ${c.c_postalcode}</p>
                    </div>
                `,
                confirmButtonText: 'ปิด'
            });
        }
    },

    addCustomerPrompt: function() {
        Swal.fire({
            title: 'เพิ่มลูกค้าใหม่',
            html: `
                <div class="text-start mb-2"><label class="small fw-bold">ชื่อบริษัท:</label><input id="swal-cust-name" class="form-control" placeholder="ชื่อบริษัท"></div>
                <div class="text-start mb-2"><label class="small fw-bold">ชื่อผู้ติดต่อ:</label><input id="swal-cust-contact" class="form-control" placeholder="ชื่อผู้ติดต่อ"></div>
                <div class="text-start mb-2"><label class="small fw-bold">เมือง:</label><input id="swal-cust-city" class="form-control" placeholder="เมือง"></div>
                <div class="text-start mb-2"><label class="small fw-bold">ประเทศ:</label><input id="swal-cust-country" class="form-control" placeholder="เช่น Thailand, Germany"></div>
                <div class="text-start"><label class="small fw-bold">รหัสไปรษณีย์:</label><input id="swal-cust-postal" class="form-control" placeholder="รหัสไปรษณีย์"></div>
            `,
            showCancelButton: true,
            confirmButtonText: '➕ บันทึกข้อมูล',
            cancelButtonText: 'ยกเลิก',
            preConfirm: () => {
                const name = document.getElementById('swal-cust-name').value.trim();
                const contact = document.getElementById('swal-cust-contact').value.trim();
                if (!name || !contact) { Swal.showValidationMessage('กรุณากรอกชื่อบริษัทและผู้ติดต่อ'); return false; }
                return {
                    c_customername: name,
                    c_contactname: contact,
                    c_city: document.getElementById('swal-cust-city').value.trim(),
                    c_country: document.getElementById('swal-cust-country').value.trim(),
                    c_postalcode: document.getElementById('swal-cust-postal').value.trim()
                };
            }
        }).then(async (result) => {
            if (result.isConfirmed) {
                const res = await API.post('/customers', result.value);
                if (res && res.status) {
                    Swal.fire('สำเร็จ', 'เพิ่มข้อมูลลูกค้าเรียบร้อยแล้ว', 'success');
                    App.loadCustomers();
                }
            }
        });
    },

    // ==========================================
    // 09: Suppliers
    // ==========================================
    loadSuppliers: async function() {
        const search = document.getElementById('sup-search-input')?.value || '';
        const res = await API.get('/suppliers', { search });
        if (!res || !res.status) return;

        const sups = res.data || [];
        const countEl = document.getElementById('sup-total-count');
        if (countEl) countEl.textContent = `${sups.length} ราย`;

        const tbody = document.getElementById('suppliers-table-tbody');
        if (tbody) {
            tbody.innerHTML = sups.map(s => `
                <tr>
                    <td style="font-family:'IBM Plex Mono'">${s.i_SupplierID}</td>
                    <td style="font-weight:600">${s.c_SupplierName}</td>
                    <td>${s.c_ContactName}</td>
                    <td>${s.c_City}</td>
                    <td>${s.c_Country}</td>
                    <td style="font-family:'IBM Plex Mono'">${s.c_Phone}</td>
                    <td style="text-align:center">${s.product_count || 3}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-light" onclick="App.viewSupplier(${s.i_SupplierID})">👁</button>
                    </td>
                </tr>
            `).join('');
        }
    },

    viewSupplier: async function(id) {
        const res = await API.get(`/suppliers/${id}`);
        if (res && res.status && res.data) {
            const s = res.data;
            Swal.fire({
                title: `ข้อมูลผู้จัดจำหน่าย #${s.i_SupplierID}`,
                html: `
                    <div class="text-start">
                        <p><strong>ชื่อบริษัท:</strong> ${s.c_SupplierName}</p>
                        <p><strong>ผู้ติดต่อ:</strong> ${s.c_ContactName}</p>
                        <p><strong>เบอร์โทรศัพท์:</strong> ${s.c_Phone}</p>
                        <p><strong>ที่อยู่:</strong> ${s.c_Address || '—'}</p>
                        <p><strong>เมือง / ประเทศ:</strong> ${s.c_City}, ${s.c_Country}</p>
                        <p><strong>จำนวนสินค้าที่จัดส่ง:</strong> ${s.product_count || 0} รายการ</p>
                    </div>
                `,
                confirmButtonText: 'ปิด'
            });
        }
    },

    // ==========================================
    // 10: Employees
    // ==========================================
    loadEmployees: async function() {
        const res = await API.get('/employees');
        if (!res || !res.status) return;

        const emps = res.data || [];
        const countEl = document.getElementById('emp-total-count');
        if (countEl) countEl.textContent = `${emps.length} คน`;

        const grid = document.getElementById('employees-grid');
        if (grid) {
            grid.innerHTML = emps.map(e => `
                <div class="emp-card">
                    <div class="emp-avatar" style="background:${e.avatar_bg || '#2563EB'}">${e.initials}</div>
                    <div class="emp-name">${e.c_FirstName} ${e.c_LastName}</div>
                    <div class="emp-title">${e.title || 'พนักงานขาย'}</div>
                    <div class="emp-meta">เกิด: ${e.c_BirthDate}</div>
                    <div class="mt-2 text-muted small" style="overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">
                        ${e.c_Notes || ''}
                    </div>
                </div>
            `).join('');
        }
    },

    // ==========================================
    // 11: Shippers
    // ==========================================
    loadShippers: async function() {
        const res = await API.get('/shippers');
        if (!res || !res.status) return;

        const shippers = res.data || [];
        const countEl = document.getElementById('shipper-total-count');
        if (countEl) countEl.textContent = `${shippers.length} บริษัท`;

        const grid = document.getElementById('shippers-grid');
        if (grid) {
            grid.innerHTML = shippers.map(s => `
                <div class="col">
                    <div class="shipper-card">
                        <div style="font-size:36px;margin-bottom:12px">${s.icon || '🚚'}</div>
                        <div style="font-size:16px;font-weight:700;color:#ffffff">${s.c_ShipperName}</div>
                        <div style="font-size:13px;color:var(--subtitle);margin-top:4px">${s.c_Phone}</div>
                        <div style="margin-top:14px;display:flex;justify-content:space-between;align-items:center">
                            <span style="font-size:12px;color:var(--ink-3)">จัดส่งแล้ว ${s.delivery_count || 50} ครั้ง</span>
                            <span class="badge-bms badge-green">${s.status || 'พร้อมให้บริการ'}</span>
                        </div>
                    </div>
                </div>
            `).join('');
        }
    },

    // ==========================================
    // 12: Reports & Analytics
    // ==========================================
    loadReports: async function() {
        const year = document.getElementById('report-filter-year')?.value || 'all';
        const res = await API.get('/reports/summary', { year });
        if (!res || !res.status || !res.data) return;

        const r = res.data;
        document.getElementById('rep-kpi-revenue').textContent = r.kpis.total_revenue;
        document.getElementById('rep-kpi-orders').textContent = r.kpis.total_orders;
        document.getElementById('rep-kpi-avg').textContent = r.kpis.avg_order_value;
        document.getElementById('rep-kpi-shipper').textContent = r.kpis.top_shipper;
        document.getElementById('rep-kpi-shipper-trips').textContent = `${r.kpis.top_shipper_trips} ครั้ง`;

        const prodWrap = document.getElementById('rep-top-products-wrap');
        if (prodWrap && r.top_products) {
            prodWrap.innerHTML = r.top_products.map(p => `
                <div class="prog-row">
                    <div class="prog-label">
                        <span>${p.name}</span>
                        <span style="font-weight:700;font-family:'IBM Plex Mono'">${p.units} หน่วย</span>
                    </div>
                    <div class="prog-bar">
                        <div class="prog-fill" style="width:${p.percent}%"></div>
                    </div>
                </div>
            `).join('');
        }

        const custTbody = document.getElementById('rep-top-customers-tbody');
        if (custTbody && r.top_customers) {
            custTbody.innerHTML = r.top_customers.map(c => `
                <tr>
                    <td style="font-weight:700;font-size:16px">${c.rank}</td>
                    <td style="font-weight:600">${c.company}</td>
                    <td>${c.country}</td>
                    <td style="text-align:center">${c.orders}</td>
                    <td>
                        <div class="prog-bar" style="width:120px">
                            <div class="prog-fill" style="width:${c.percent}%"></div>
                        </div>
                    </td>
                </tr>
            `).join('');
        }
    },

    exportReportCSV: function() {
        const baseUrl = API.getBaseUrl();
        window.open(`${baseUrl}/reports/export`, '_blank');
    },

    exportReportPDF: function() {
        window.print();
    },

    // Pagination Helper
    renderPagination: function(containerId, total, currentPage, limit, onPageClick) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const totalPages = Math.max(1, Math.ceil(total / limit));
        const start = (currentPage - 1) * limit + 1;
        const end = Math.min(total, currentPage * limit);

        let html = `<span class="small text-secondary">แสดง ${total > 0 ? start : 0}–${end} จาก ${total} รายการ</span><div class="d-flex gap-1">`;
        html += `<button class="btn btn-sm btn-outline-secondary ${currentPage <= 1 ? 'disabled' : ''}" onclick="(${onPageClick})(${currentPage - 1})">‹</button>`;

        for (let i = 1; i <= Math.min(5, totalPages); i++) {
            html += `<button class="btn btn-sm ${i === currentPage ? 'btn-light fw-bold text-dark' : 'btn-outline-secondary'}" onclick="(${onPageClick})(${i})">${i}</button>`;
        }

        html += `<button class="btn btn-sm btn-outline-secondary ${currentPage >= totalPages ? 'disabled' : ''}" onclick="(${onPageClick})(${currentPage + 1})">›</button>`;
        html += `</div>`;
        container.innerHTML = html;
    }
};

document.addEventListener('DOMContentLoaded', () => {
    App.init();
});
