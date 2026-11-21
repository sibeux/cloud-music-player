/**
 * Explore Albums Component
 * Menampilkan 5 section album terbaru berdasarkan kategori dengan Lazy Load & Keyset Pagination.
 * Kategori yang didukung:
 *  1: IndoPride
 *  2: 日本の歌
 *  3: Jowo Mletre
 *  4: Worldwide
 *  5: Instrumental
 */

(function () {
    'use strict';

    const CATEGORIES = [
        { id: 1, name: 'IndoPride', label: 'Indonesian Albums' },
        { id: 2, name: '日本の歌', label: 'Japanese Albums' },
        { id: 3, name: 'Jowo Mletre', label: 'Javanese Albums' },
        { id: 4, name: 'Worldwide', label: 'Worldwide Albums' },
        { id: 5, name: 'Instrumental', label: 'Instrumental Albums' }
    ];

    const API_ENDPOINT = '/api/music/album/explore';
    const LIMIT_PER_PAGE = 10;

    // State per category: { cursor, hasMore, isLoading, isLoaded }
    const categoryStates = {};

    CATEGORIES.forEach(cat => {
        categoryStates[cat.id] = {
            cursor: null,
            hasMore: true,
            isLoading: false,
            isLoaded: false
        };
    });

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    /**
     * Membuat kerangka skeleton loading cards.
     */
    function renderSkeletons(count = 6) {
        let html = '';
        for (let i = 0; i < count; i++) {
            html += `
                <div class="explore-skeleton-card">
                    <div class="explore-skeleton-cover"></div>
                    <div class="explore-skeleton-text"></div>
                    <div class="explore-skeleton-text short"></div>
                </div>
            `;
        }
        return html;
    }

    /**
     * Membuat HTML untuk 1 buah album card.
     */
    function createAlbumCardHtml(album) {
        const coverSrc = album.cover || 'images/default-album.png';
        const discBadge = album.have_disc ? '<span class="explore-album-disc-badge"><i class="fas fa-compact-disc"></i> Disc</span>' : '';

        return `
            <div class="explore-album-card" data-album-id="${escapeHtml(album.id)}" title="${escapeHtml(album.title)} - ${escapeHtml(album.author)}">
                <div class="explore-album-cover-wrap">
                    <img class="explore-album-cover" src="${escapeHtml(coverSrc)}" alt="${escapeHtml(album.title)}" loading="lazy" onerror="this.src='images/cybeat.png'">
                    ${discBadge}
                </div>
                <div class="explore-album-info">
                    <h4 class="explore-album-title">${escapeHtml(album.title)}</h4>
                    <p class="explore-album-author">${escapeHtml(album.author || 'Various Artists')}</p>
                </div>
            </div>
        `;
    }

    /**
     * Render container utama untuk 5 section kategori.
     */
    function initSections(container) {
        let sectionsHtml = '';

        CATEGORIES.forEach(cat => {
            sectionsHtml += `
                <section class="explore-section" id="explore-section-${cat.id}" data-category-id="${cat.id}">
                    <div class="explore-section-header">
                        <div class="explore-section-title-wrap">
                            <h3 class="explore-section-title">${escapeHtml(cat.name)}</h3>
                            <span class="explore-category-badge">${escapeHtml(cat.label)}</span>
                        </div>
                    </div>
                    <div class="explore-album-grid" id="explore-grid-${cat.id}">
                        ${renderSkeletons(6)}
                    </div>
                    <div class="explore-show-more-wrap" id="explore-more-wrap-${cat.id}" style="display: none;">
                        <button type="button" class="explore-show-more-btn" id="explore-btn-${cat.id}" data-category-id="${cat.id}">
                            <span>Show More</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                </section>
            `;
        });

        container.innerHTML = sectionsHtml;

        // Pasang event listener untuk tombol Show More
        CATEGORIES.forEach(cat => {
            const btn = document.getElementById(`explore-btn-${cat.id}`);
            if (btn) {
                btn.addEventListener('click', function () {
                    loadAlbumsForCategory(cat.id, false);
                });
            }
        });

        // Setup Intersection Observer untuk Lazy Load tiap section
        setupIntersectionObserver();
    }

    /**
     * Setup IntersectionObserver: Section hanya di-fetch ketika masuk viewport.
     */
    function setupIntersectionObserver() {
        if (!('IntersectionObserver' in window)) {
            // Fallback untuk browser lawas: fetch semua section
            CATEGORIES.forEach(cat => loadAlbumsForCategory(cat.id, true));
            return;
        }

        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const categoryId = parseInt(entry.target.getAttribute('data-category-id'), 10);
                    if (categoryId && !categoryStates[categoryId].isLoaded) {
                        loadAlbumsForCategory(categoryId, true);
                        obs.unobserve(entry.target); // Lepas observasi setelah trigger pertama
                    }
                }
            });
        }, {
            root: null,
            rootMargin: '150px 0px', // Preload sedikit sebelum benar-benar terlihat
            threshold: 0.05
        });

        CATEGORIES.forEach(cat => {
            const sectionEl = document.getElementById(`explore-section-${cat.id}`);
            if (sectionEl) {
                observer.observe(sectionEl);
            }
        });
    }

    /**
     * Fetch album dari endpoint `/api/music/album/explore`.
     */
    function loadAlbumsForCategory(categoryId, isInitial = false) {
        const state = categoryStates[categoryId];
        if (!state || state.isLoading) return;

        state.isLoading = true;

        const gridEl = document.getElementById(`explore-grid-${categoryId}`);
        const moreWrapEl = document.getElementById(`explore-more-wrap-${categoryId}`);
        const btnEl = document.getElementById(`explore-btn-${categoryId}`);

        // State UI saat loading
        if (!isInitial && btnEl) {
            btnEl.disabled = true;
            btnEl.innerHTML = `<span class="explore-btn-spinner"></span> Loading...`;
        }

        let url = `${API_ENDPOINT}?category_id=${categoryId}&limit=${LIMIT_PER_PAGE}`;
        if (!isInitial && state.cursor) {
            url += `&cursor=${state.cursor}`;
        }

        fetch(url)
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                return res.json();
            })
            .then(response => {
                if (response.status !== 'success' || !response.data) {
                    throw new Error(response.message || 'Gagal memuat data');
                }

                const data = response.data;
                const albums = data.albums || [];

                // Jika initial load, hapus skeletons
                if (isInitial) {
                    gridEl.innerHTML = '';
                    state.isLoaded = true;
                }

                if (albums.length === 0 && isInitial) {
                    gridEl.innerHTML = '<p style="color: #777; grid-column: 1/-1; padding: 20px 0;">Belum ada album pada kategori ini.</p>';
                    if (moreWrapEl) moreWrapEl.style.display = 'none';
                    return;
                }

                // Append card album baru
                let cardsHtml = '';
                albums.forEach(album => {
                    cardsHtml += createAlbumCardHtml(album);
                });
                gridEl.insertAdjacentHTML('beforeend', cardsHtml);

                // Update state pagination
                state.cursor = data.next_cursor;
                state.hasMore = Boolean(data.has_more);

                // Atur visibilitas tombol Show More
                if (moreWrapEl) {
                    if (state.hasMore) {
                        moreWrapEl.style.display = 'block';
                    } else {
                        moreWrapEl.style.display = 'none';
                    }
                }
            })
            .catch(err => {
                console.error(`[ExploreAlbums] Error category ${categoryId}:`, err);
                if (isInitial) {
                    gridEl.innerHTML = '<p style="color: #ff5555; grid-column: 1/-1;">Gagal memuat album. Silakan coba lagi.</p>';
                }
            })
            .finally(() => {
                state.isLoading = false;
                if (btnEl) {
                    btnEl.disabled = false;
                    btnEl.innerHTML = `<span>Show More</span> <i class="fas fa-chevron-down"></i>`;
                }
            });
    }

    // Auto-mount jika container `#explore-sections-container` ada di DOM
    document.addEventListener('DOMContentLoaded', function () {
        const targetContainer = document.getElementById('explore-sections-container');
        if (targetContainer) {
            initSections(targetContainer);
        }
    });

    // Ekspor fungsi agar bisa dipanggil secara manual jika diperlukan
    window.ExploreAlbums = {
        init: initSections,
        reloadCategory: function (catId) {
            if (categoryStates[catId]) {
                categoryStates[catId].cursor = null;
                categoryStates[catId].hasMore = true;
                categoryStates[catId].isLoaded = false;
                loadAlbumsForCategory(catId, true);
            }
        }
    };
})();
