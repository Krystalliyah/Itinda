<script setup lang="ts">
import {
    TagIcon,
    PlusIcon,
    PencilSquareIcon,
    ChevronDownIcon,
    ChevronRightIcon,
    FolderIcon,
    FolderOpenIcon,
    XMarkIcon,
    CheckIcon,
    MagnifyingGlassIcon,
    Squares2X2Icon,
} from '@heroicons/vue/24/outline';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, reactive } from 'vue';
import Header from '@/components/Header.vue';
import AdminNav from '@/components/navigation/AdminNav.vue';
import Sidebar from '@/components/Sidebar.vue';
import { useSidebar } from '@/composables/useSidebar';

interface Category {
    id: number
    name: string
    slug: string
    description: string
    color: string
    parent_id: number | null
    children: Category[]
    product_count: number
}

interface Props {
    categories?: Category[]
}

const props = defineProps<Props>()
const allCategories = computed(() => props.categories ?? [])

const { isCollapsed } = useSidebar()
const contentClass = computed(() => ({
    'dashboard-content': true,
    'sidebar-collapsed': isCollapsed.value,
}))

const expanded = ref<Set<number>>(new Set())
allCategories.value.forEach(c => { if (c.children.length) expanded.value.add(c.id) })

function toggleExpand(id: number) {
    if (expanded.value.has(id)) expanded.value.delete(id)
    else expanded.value.add(id)
}

const search = ref('')
const filteredCategories = computed(() => {
    const q = search.value.trim().toLowerCase()
    if (!q) return allCategories.value
    function matchCat(c: Category): Category | null {
        const matchSelf = c.name.toLowerCase().includes(q) || c.slug.toLowerCase().includes(q)
        const matchedChildren = c.children.map(matchCat).filter(Boolean) as Category[]
        if (matchSelf || matchedChildren.length) return { ...c, children: matchedChildren }
        return null
    }
    return allCategories.value.map(matchCat).filter(Boolean) as Category[]
})

const totalParents  = computed(() => allCategories.value.length)
const totalChildren = computed(() => allCategories.value.reduce((s, c) => s + c.children.length, 0))
const totalProducts = computed(() =>
    allCategories.value.reduce((s, c) =>
        s + c.product_count + c.children.reduce((cs, ch) => cs + ch.product_count, 0), 0)
)

const COLORS = [
    '#6366f1', '#ec4899', '#f59e0b', '#10b981',
    '#3b82f6', '#ef4444', '#8b5cf6', '#06b6d4',
    '#84cc16', '#f97316',
]

type ModalMode = 'create-parent' | 'create-child' | 'edit'
const modal = reactive({
    open: false,
    mode: 'create-parent' as ModalMode,
    parentId: null as number | null,
    parentName: '',
    editId: null as number | null,
    name: '',
    slug: '',
    description: '',
    color: '#6366f1',
    saving: false,
    errors: {} as Record<string, string>,
})

function openCreateParent() {
    Object.assign(modal, { open: true, mode: 'create-parent', parentId: null, parentName: '', editId: null, name: '', slug: '', description: '', color: '#6366f1', saving: false, errors: {} })
}
function openCreateChild(parent: Category) {
    Object.assign(modal, { open: true, mode: 'create-child', parentId: parent.id, parentName: parent.name, editId: null, name: '', slug: '', description: '', color: parent.color, saving: false, errors: {} })
}
function openEdit(cat: Category, parentId: number | null = null) {
    const parent = parentId !== null ? allCategories.value.find(c => c.id === parentId) : null
    Object.assign(modal, { open: true, mode: 'edit', parentId, parentName: parent?.name ?? '', editId: cat.id, name: cat.name, slug: cat.slug, description: cat.description, color: cat.color, saving: false, errors: {} })
}
function closeModal() { modal.open = false }

function onNameInput() {
    if (modal.mode !== 'edit') {
        modal.slug = modal.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')
    }
}

function validate(): boolean {
    modal.errors = {}
    if (!modal.name.trim()) modal.errors.name = 'Name is required.'
    if (!modal.slug.trim()) modal.errors.slug = 'Slug is required.'
    else if (!/^[a-z0-9-]+$/.test(modal.slug)) modal.errors.slug = 'Slug may only contain lowercase letters, numbers and hyphens.'
    return Object.keys(modal.errors).length === 0
}

function saveCategory() {
    if (!validate()) return
    modal.saving = true
    const payload = { name: modal.name, slug: modal.slug, description: modal.description, color: modal.color, parent_id: modal.mode === 'create-child' ? modal.parentId : null }
    const options = {
        onSuccess: () => closeModal(),
        onError: (errors: Record<string, string>) => { modal.errors = errors },
        onFinish: () => { modal.saving = false },
    }
    if (modal.mode === 'edit' && modal.editId !== null) {
        router.put(`/admin/categories/${modal.editId}`, payload, options)
    } else {
        router.post('/admin/categories', payload, options)
    }
}

const modalTitle = computed(() => {
    if (modal.mode === 'create-parent') return 'New Category'
    if (modal.mode === 'create-child') return `New Subcategory under "${modal.parentName}"`
    return 'Edit Category'
})
</script>

<template>
    <Head title="Product Categories" />

    <div class="dashboard-wrapper">
        <Header />
        <Sidebar role="admin">
            <AdminNav />
        </Sidebar>

        <main :class="contentClass">
            <div class="page-container">

                <!-- Page Header -->
                <div class="page-header">
                    <div class="page-header-icon">
                        <Squares2X2Icon class="header-icon" />
                    </div>
                    <div>
                        <h1>Product Categories</h1>
                        <p>Organise your product taxonomy with parent categories and subcategories</p>
                    </div>
                    <button class="cat-btn-primary" @click="openCreateParent">
                        <PlusIcon class="btn-icon" /> New Category
                    </button>
                </div>

                <!-- Stats Strip — shadow-sm makes global dark CSS pick it up, same as Customers.vue -->
                <div class="stats-strip shadow-sm">
                    <div class="strip-stat">
                        <span class="strip-val">{{ totalParents }}</span>
                        <span class="strip-label">Parent Categories</span>
                    </div>
                    <div class="strip-divider"></div>
                    <div class="strip-stat">
                        <span class="strip-val">{{ totalChildren }}</span>
                        <span class="strip-label">Subcategories</span>
                    </div>
                    <div class="strip-divider"></div>
                    <div class="strip-stat">
                        <span class="strip-val">{{ totalProducts }}</span>
                        <span class="strip-label">Total Products</span>
                    </div>
                </div>

                <!-- Tree Card — shadow-sm makes global dark CSS pick it up, same as Customers.vue -->
                <div class="card shadow-sm">
                    <!-- Toolbar -->
                    <div class="tree-toolbar">
                        <div class="search-wrap">
                            <MagnifyingGlassIcon class="search-icon" />
                            <input
                                v-model="search"
                                class="search-input"
                                placeholder="Search categories…"
                                type="text"
                            />
                        </div>
                        <span class="tree-count">
                            {{ filteredCategories.length }} parent{{ filteredCategories.length !== 1 ? 's' : '' }}
                        </span>
                    </div>

                    <!-- Empty state -->
                    <div v-if="filteredCategories.length === 0" class="empty-state">
                        <TagIcon class="empty-icon" />
                        <p v-if="search">No categories match "{{ search }}"</p>
                        <p v-else>No categories yet. <button class="empty-cta" @click="openCreateParent">Create one →</button></p>
                    </div>

                    <!-- Tree -->
                    <ul v-else class="tree-list">
                        <li
                            v-for="cat in filteredCategories"
                            :key="cat.id"
                            class="tree-parent-item"
                            :style="{ '--cat-color': cat.color }"
                        >
                            <div class="tree-row parent-row">
                                <button
                                    class="expand-btn"
                                    :disabled="cat.children.length === 0"
                                    @click="cat.children.length && toggleExpand(cat.id)"
                                >
                                    <ChevronDownIcon v-if="expanded.has(cat.id) && cat.children.length" class="chevron" />
                                    <ChevronRightIcon v-else-if="cat.children.length" class="chevron" />
                                    <span v-else class="chevron-spacer"></span>
                                </button>

                                <span class="cat-swatch" :style="{ background: cat.color }"></span>

                                <FolderOpenIcon v-if="expanded.has(cat.id) && cat.children.length" class="folder-icon" :style="{ color: cat.color }" />
                                <FolderIcon v-else class="folder-icon" :style="{ color: cat.color }" />

                                <div class="tree-info">
                                    <span class="tree-name">{{ cat.name }}</span>
                                    <span class="tree-slug">{{ cat.slug }}</span>
                                </div>

                                <span v-if="cat.description" class="tree-desc">{{ cat.description }}</span>

                                <div class="tree-chips">
                                    <span v-if="cat.children.length" class="chip chip-children">{{ cat.children.length }} sub</span>
                                    <span class="chip chip-products">{{ cat.product_count }} products</span>
                                </div>

                                <div class="tree-actions">
                                    <button class="action-btn action-sub" @click="openCreateChild(cat)">
                                        <PlusIcon class="action-icon" />
                                        <span class="action-label">Add sub</span>
                                    </button>
                                    <button class="action-btn action-edit" @click="openEdit(cat, null)">
                                        <PencilSquareIcon class="action-icon" />
                                    </button>
                                </div>
                            </div>

                            <ul v-if="cat.children.length && expanded.has(cat.id)" class="child-list">
                                <li v-for="child in cat.children" :key="child.id" class="tree-child-item">
                                    <div class="tree-row child-row">
                                        <span class="child-connector" :style="{ borderColor: cat.color + '50' }"></span>
                                        <span class="child-dot" :style="{ background: cat.color }"></span>
                                        <div class="tree-info">
                                            <span class="tree-name child-name">{{ child.name }}</span>
                                            <span class="tree-slug">{{ child.slug }}</span>
                                        </div>
                                        <span v-if="child.description" class="tree-desc">{{ child.description }}</span>
                                        <div class="tree-chips">
                                            <span class="chip chip-products">{{ child.product_count }} products</span>
                                        </div>
                                        <div class="tree-actions">
                                            <button class="action-btn action-edit" @click="openEdit(child, cat.id)">
                                                <PencilSquareIcon class="action-icon" />
                                            </button>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>

            </div>
        </main>
    </div>

    <!-- Modal -->
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="modal.open" class="modal-overlay" @click.self="closeModal">
                <div class="cat-modal shadow-sm" role="dialog" :aria-label="modalTitle">

                    <div class="modal-header">
                        <div class="modal-title-group">
                            <div class="modal-icon-wrap" :style="{ background: modal.color + '1a' }">
                                <TagIcon class="modal-icon" :style="{ color: modal.color }" />
                            </div>
                            <h2 class="modal-title">{{ modalTitle }}</h2>
                        </div>
                        <button class="modal-close-btn" @click="closeModal">
                            <XMarkIcon class="close-icon" />
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="field">
                            <label class="field-label">Category Name <span class="required">*</span></label>
                            <input v-model="modal.name" @input="onNameInput" class="field-input" :class="{ 'field-error': modal.errors.name }" placeholder="e.g. Beverages" type="text" autofocus />
                            <p v-if="modal.errors.name" class="error-msg">{{ modal.errors.name }}</p>
                        </div>

                        <div class="field">
                            <label class="field-label">Slug <span class="required">*</span></label>
                            <div class="slug-wrap" :class="{ 'field-error-wrap': modal.errors.slug }">
                                <span class="slug-prefix">/categories/</span>
                                <input v-model="modal.slug" class="field-input slug-input" placeholder="beverages" type="text" />
                            </div>
                            <p v-if="modal.errors.slug" class="error-msg">{{ modal.errors.slug }}</p>
                        </div>

                        <div class="field">
                            <label class="field-label">Description</label>
                            <textarea v-model="modal.description" class="field-input field-textarea" placeholder="Brief description of this category…" rows="2"></textarea>
                        </div>

                        <div class="field">
                            <label class="field-label">Colour</label>
                            <div class="color-picker">
                                <button
                                    v-for="c in COLORS"
                                    :key="c"
                                    class="color-swatch-btn"
                                    :class="{ selected: modal.color === c }"
                                    :style="{ backgroundColor: c }"
                                    type="button"
                                    @click="modal.color = c"
                                >
                                    <CheckIcon v-if="modal.color === c" class="swatch-check" />
                                </button>
                            </div>
                        </div>

                        <div class="field-preview">
                            <span class="preview-label">Preview</span>
                            <div class="preview-pill" :style="{ background: modal.color + '18', border: `1.5px solid ${modal.color}40`, color: modal.color }">
                                <span class="preview-dot" :style="{ background: modal.color }"></span>
                                {{ modal.name || 'Category Name' }}
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="cat-btn-ghost" type="button" @click="closeModal">Cancel</button>
                        <button class="cat-btn-primary" type="button" :disabled="modal.saving" @click="saveCategory">
                            <CheckIcon class="btn-icon" />
                            {{ modal.saving ? 'Saving…' : (modal.mode === 'edit' ? 'Save Changes' : 'Create Category') }}
                        </button>
                    </div>

                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

* { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }

.page-container {
    padding: 2rem 2.5rem;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* ── Page Header ── */
.page-header {
    display: flex;
    align-items: center;
    gap: 1rem;
}
.page-header-icon {
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    border-radius: 12px;
    padding: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.header-icon { width: 28px; height: 28px; color: white; }
.page-header h1 { font-size: 1.75rem; font-weight: 700; color: var(--brand-green-dark); margin: 0 0 0.25rem 0; }
.page-header p  { color: var(--brand-muted); margin: 0; font-size: 0.95rem; }

:global(.dark) .page-header h1 { color: var(--foreground) !important; }
:global(.dark) .page-header p  { color: var(--muted-foreground) !important; }

/* ── Buttons (unique names to avoid global .dark button reset) ── */
.cat-btn-primary {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.55rem 1.1rem;
    background-color: #6366f1; color: white;
    font-size: 0.85rem; font-weight: 600;
    border: none; border-radius: 10px; cursor: pointer;
    transition: background-color 0.15s, transform 0.12s;
    white-space: nowrap; flex-shrink: 0; margin-left: auto;
}
.cat-btn-primary:hover:not(:disabled) { background-color: #4f46e5; transform: translateY(-1px); }
.cat-btn-primary:disabled { opacity: 0.65; cursor: not-allowed; }

.cat-btn-ghost {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.55rem 1.1rem;
    background-color: transparent; color: var(--muted-foreground);
    font-size: 0.85rem; font-weight: 600;
    border: 1px solid var(--border); border-radius: 10px; cursor: pointer;
    transition: background-color 0.15s;
}
.cat-btn-ghost:hover { background-color: var(--accent); }

.btn-icon { width: 16px; height: 16px; }

/* ── Stats Strip ── */
.stats-strip {
    display: flex; align-items: center;
    background-color: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 1rem 1.75rem;
}

.strip-stat    { display: flex; flex-direction: column; gap: 0.15rem; flex: 1; }
.strip-val     { font-size: 1.6rem; font-weight: 700; color: var(--foreground); line-height: 1; }
.strip-label   { font-size: 0.72rem; font-weight: 600; color: var(--muted-foreground); text-transform: uppercase; letter-spacing: 0.05em; }

.strip-divider { width: 1px; height: 40px; background-color: var(--border); margin: 0 2rem; flex-shrink: 0; }

/* ── Card ── */
.card {
    background-color: var(--card);
    border-radius: 14px;
    border: 1px solid var(--border);
    overflow: hidden;
}

/* ── Toolbar ── */
.tree-toolbar {
    display: flex; align-items: center; gap: 1rem;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border);
}

.search-wrap { position: relative; flex: 1; max-width: 320px; }
.search-icon { position: absolute; left: 0.7rem; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: var(--muted-foreground); pointer-events: none; }

.search-input {
    width: 100%; padding: 0.5rem 0.75rem 0.5rem 2.2rem;
    background-color: var(--secondary); border: 1px solid var(--border);
    border-radius: 9px; font-size: 0.85rem; color: var(--foreground);
    outline: none; transition: border-color 0.15s, box-shadow 0.15s;
    box-sizing: border-box;
}
.search-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.15); background-color: var(--card); }
.search-input::placeholder { color: var(--muted-foreground); opacity: 0.6; }

.tree-count { font-size: 0.78rem; font-weight: 500; color: var(--muted-foreground); margin-left: auto; white-space: nowrap; }

/* ── Empty state ── */
.empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.5rem; padding: 4rem 1rem; }
.empty-icon { width: 40px; height: 40px; color: var(--border); }
.empty-state p { font-size: 0.88rem; color: var(--muted-foreground); margin: 0; }
.empty-cta { background: none; border: none; color: #6366f1; font-weight: 600; cursor: pointer; font-size: inherit; }

/* ── Tree ── */
.tree-list { list-style: none; margin: 0; padding: 0; }
.tree-parent-item { border-bottom: 1px solid var(--border); }
.tree-parent-item:last-child { border-bottom: none; }

.tree-row { display: flex; align-items: center; gap: 0.6rem; padding: 0.85rem 1.5rem; transition: background-color 0.13s; }
.tree-row:hover { background-color: var(--accent); }

.parent-row { border-left: 3px solid var(--cat-color, var(--border)); }

.expand-btn {
    background-color: transparent !important; border: none !important; box-shadow: none !important;
    cursor: pointer; padding: 0.1rem; display: flex; align-items: center;
    flex-shrink: 0; color: var(--muted-foreground); transition: color 0.15s; width: 20px;
}
.expand-btn:hover:not(:disabled) { color: var(--foreground); }
.expand-btn:disabled { cursor: default; }

.chevron { width: 14px; height: 14px; }
.chevron-spacer { display: inline-block; width: 14px; height: 14px; }
.cat-swatch { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.folder-icon { width: 18px; height: 18px; flex-shrink: 0; }

.tree-info { display: flex; flex-direction: column; gap: 0.05rem; width: 160px; flex-shrink: 0; min-width: 0; }
.tree-name { font-size: 0.88rem; font-weight: 600; color: var(--foreground); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tree-slug { font-size: 0.7rem; color: var(--muted-foreground); font-family: ui-monospace, monospace; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.child-name { font-size: 0.84rem; }

.tree-desc { flex: 1; font-size: 0.78rem; color: var(--muted-foreground); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; min-width: 0; }

.tree-chips { display: flex; gap: 0.4rem; flex-shrink: 0; }
.chip { font-size: 0.67rem; font-weight: 600; padding: 0.18rem 0.5rem; border-radius: 99px; white-space: nowrap; }
.chip-children { background-color: #ede9fe; color: #5b21b6; }
.chip-products  { background-color: var(--secondary); color: var(--muted-foreground); }

.tree-actions { display: flex; gap: 0.4rem; flex-shrink: 0; opacity: 0; transition: opacity 0.15s; }
.tree-row:hover .tree-actions { opacity: 1; }

.action-btn {
    display: inline-flex; align-items: center; gap: 0.3rem;
    padding: 0.28rem 0.6rem;
    border: 1px solid var(--border); border-radius: 7px;
    background-color: var(--card); cursor: pointer;
    font-size: 0.72rem; font-weight: 600;
    transition: background-color 0.13s, border-color 0.13s;
}
.action-sub  { color: #6366f1; }
.action-sub:hover  { background-color: #ede9fe; border-color: #c4b5fd; }
.action-edit { color: var(--muted-foreground); }
.action-edit:hover { background-color: var(--accent); border-color: var(--muted-foreground); }
.action-icon  { width: 14px; height: 14px; }
.action-label { font-size: 0.72rem; }

/* ── Children ── */
.child-list { list-style: none; margin: 0; padding: 0; }
.tree-child-item { border-top: 1px solid var(--border); }

.child-row { padding-left: 3.5rem; background-color: var(--secondary); }
.child-row:hover { background-color: var(--accent); }

.child-connector { width: 0; height: 100%; flex-shrink: 0; border-left: 2px dashed; margin-right: -0.3rem; }
.child-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }

/* ── Modal ── */
.modal-overlay {
    position: fixed; inset: 0;
    background: rgba(15,23,42,0.45);
    backdrop-filter: blur(3px);
    display: flex; align-items: center; justify-content: center;
    z-index: 9999; padding: 1rem;
}
:global(.dark) .modal-overlay { background: rgba(0,0,0,0.65); }

.cat-modal {
    background-color: var(--card);
    border-radius: 18px; width: 100%; max-width: 480px;
    border: 1px solid var(--border);
    box-shadow: 0 20px 60px rgba(0,0,0,0.18);
    display: flex; flex-direction: column; overflow: hidden;
}

.modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--border);
}
:global(.dark) .modal-header { border-bottom-color: var(--border) !important; }

.modal-title-group { display: flex; align-items: center; gap: 0.75rem; }
.modal-icon-wrap { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.modal-icon { width: 20px; height: 20px; }
.modal-title { font-size: 1rem; font-weight: 700; color: var(--foreground); margin: 0; }

.modal-close-btn {
    background-color: transparent !important; border: none !important; box-shadow: none !important;
    cursor: pointer; padding: 0.3rem; border-radius: 8px;
    color: var(--muted-foreground); transition: background-color 0.13s, color 0.13s;
}
.modal-close-btn:hover { background-color: var(--accent) !important; color: var(--foreground); }
.close-icon { width: 20px; height: 20px; }

.modal-body { padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 1rem; overflow-y: auto; }

.modal-footer {
    display: flex; justify-content: flex-end; gap: 0.75rem;
    padding: 1rem 1.5rem; border-top: 1px solid var(--border);
}

.field { display: flex; flex-direction: column; gap: 0.35rem; }
.field-label { font-size: 0.8rem; font-weight: 600; color: var(--foreground); }
.required { color: #ef4444; }

.field-input {
    padding: 0.55rem 0.85rem; border: 1px solid var(--border); border-radius: 9px;
    font-size: 0.88rem; color: var(--foreground); background-color: var(--input);
    outline: none; transition: border-color 0.15s, box-shadow 0.15s;
    width: 100%; box-sizing: border-box;
}
.field-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.15); background-color: var(--card); }
.field-input.field-error { border-color: #ef4444; }
.field-textarea { resize: vertical; min-height: 64px; }

.error-msg { font-size: 0.74rem; color: #ef4444; margin: 0; }

.slug-wrap {
    display: flex; align-items: center;
    border: 1px solid var(--border); border-radius: 9px;
    overflow: hidden; background-color: var(--input);
}
.slug-wrap:focus-within { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.15); }
.slug-wrap.field-error-wrap { border-color: #ef4444; }

.slug-prefix {
    padding: 0.55rem 0.7rem; font-size: 0.78rem; color: var(--muted-foreground);
    background-color: var(--secondary); border-right: 1px solid var(--border);
    white-space: nowrap; flex-shrink: 0;
}

.slug-input { border: none !important; box-shadow: none !important; background-color: transparent !important; flex: 1; border-radius: 0; color: var(--foreground); }

.color-picker { display: flex; gap: 0.5rem; flex-wrap: wrap; }
.color-swatch-btn {
    width: 28px; height: 28px; border-radius: 8px;
    border: 2.5px solid transparent; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: transform 0.12s, border-color 0.12s;
}
.color-swatch-btn:hover { transform: scale(1.15); }
.color-swatch-btn.selected { border-color: var(--card); outline: 2.5px solid currentColor; transform: scale(1.1); }
.swatch-check { width: 14px; height: 14px; color: white; stroke-width: 3; }

.field-preview {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.65rem 0.85rem; background-color: var(--secondary);
    border-radius: 10px; border: 1px dashed var(--border);
}

.preview-label { font-size: 0.72rem; font-weight: 600; color: var(--muted-foreground); text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap; }
.preview-pill { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.28rem 0.75rem; border-radius: 99px; font-size: 0.82rem; font-weight: 600; }
.preview-dot  { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }

/* ── Transitions ── */
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-active .cat-modal, .modal-leave-active .cat-modal { transition: transform 0.22s cubic-bezier(.34,1.56,.64,1), opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-from .cat-modal { transform: scale(0.94) translateY(8px); opacity: 0; }
.modal-leave-to   .cat-modal { transform: scale(0.96) translateY(4px); opacity: 0; }

/* ── Responsive ── */
@media (max-width: 1024px) { .tree-desc { display: none; } }

@media (max-width: 768px) {
    .page-container { padding: 1rem; gap: 1.25rem; }
    .page-header { flex-wrap: wrap; gap: 0.75rem; }
    .page-header h1 { font-size: 1.4rem; }
    .stats-strip { padding: 0.85rem 1.25rem; }
    .strip-divider { margin: 0 1rem; }
    .strip-val { font-size: 1.3rem; }
    .tree-chips { display: none; }
    .tree-info { width: 130px; }
    .tree-toolbar { padding: 0.85rem 1rem; }
    .tree-row { padding: 0.75rem 1rem; gap: 0.5rem; }
    .child-row { padding-left: 2.5rem; }
    .tree-actions { opacity: 1; }
    .action-label { display: none; }
}

@media (max-width: 480px) {
    .page-header { flex-direction: column; align-items: flex-start; }
    .cat-btn-primary { align-self: flex-start; margin-left: 0; }
    .stats-strip { flex-direction: column; gap: 0.75rem; align-items: flex-start; }
    .strip-divider { display: none; }
    .modal-overlay { align-items: flex-end; padding: 0; }
    .cat-modal { max-width: 100%; border-radius: 16px 16px 0 0; }
    .tree-info { width: 110px; }
}

:global(.dark) .page-header h1 { color: var(--foreground); }
:global(.dark) .page-header p { color: var(--muted-foreground); }

:global(.dark) .stats-strip {
    background: var(--card);
    border-color: var(--border);
}

:global(.dark) .strip-label { color: var(--muted-foreground); }
:global(.dark) .strip-val { color: var(--foreground); }

:global(.dark) .categories-tree {
    background: var(--card);
    border-color: var(--border);
}

:global(.dark) .tree-toolbar {
    background: var(--accent);
    border-bottom-color: var(--border);
}

:global(.dark) .tree-title { color: var(--foreground); }

:global(.dark) .tree-row { border-bottom-color: var(--border); }
:global(.dark) .tree-row:hover { background: var(--accent); }

:global(.dark) .tree-name { color: var(--foreground); }
:global(.dark) .tree-slug { color: var(--muted-foreground); }

:global(.dark) .cat-modal {
    background: var(--card);
    border-color: var(--border);
}

:global(.dark) .modal-header { border-bottom-color: var(--border); }
:global(.dark) .modal-title { color: var(--foreground); }

:global(.dark) .field-label { color: var(--foreground); }
:global(.dark) .field-hint { color: var(--muted-foreground); }

:global(.dark) .chip-children { background-color: rgba(76,29,149,0.4) !important; color: #c4b5fd !important; }
:global(.dark) .action-sub:hover { background-color: rgba(99,102,241,0.2) !important; border-color: #6366f1 !important; }
</style>