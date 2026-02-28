<script setup lang="ts">
defineProps<{
    title: string;
    description: string;
    icon?: string;
}>();
</script>

<template>
    <div class="placeholder-page">
        <div class="placeholder-icon" v-if="icon" v-html="icon"></div>
        <h1>{{ title }}</h1>
        <p>{{ description }}</p>
        <div class="placeholder-badge">Coming Soon</div>
    </div>
</template>

<style scoped>
.placeholder-page {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 60vh;
    text-align: center;
    padding: 40px;
}

.placeholder-icon {
    width: 80px;
    height: 80px;
    background: rgba(27, 77, 62, 0.1);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 24px;
}

.placeholder-icon :deep(svg) {
    width: 40px;
    height: 40px;
    color: #1B4D3E;
}

h1 {
    font-family: 'Montserrat', sans-serif;
    font-size: 2.2rem;
    color: #1B4D3E;
    margin-bottom: 12px;
}

p {
    font-size: 1.1rem;
    color: #7a8f88;
    max-width: 500px;
    line-height: 1.6;
    margin-bottom: 24px;
}

.placeholder-badge {
    display: inline-block;
    padding: 8px 20px;
    background: rgba(197, 160, 89, 0.1);
    border: 1px solid rgba(197, 160, 89, 0.3);
    border-radius: 100px;
    color: #C5A059;
    font-weight: 700;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
</style>
