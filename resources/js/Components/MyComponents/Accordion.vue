<template>
    <button :id="`accordion-title-${id}`"
        class="w-full text-start px-5 mb- flex justify-between items-center text-xs rounded-r-[10px] mt-1 py-1"
        :class="active ? 'bg-gray-300 text-primary border-l-4 border-primary' : ''" @click.prevent="accordionOpen = !accordionOpen"
        :aria-expanded="accordionOpen" :aria-controls="`accordion-text-${id}`" :title="title">
        <i v-html="icon"></i>
        <p class="ml-2">
            {{ title }}
        </p>
        <i class="fa-solid fa-angle-down transform origin-center transition duration-200 ease-out"
            :class="{ '!rotate-180': accordionOpen }"></i>
    </button>
    <div :id="`accordion-text-${id}`" role="region" :aria-labelledby="`accordion-title-${id}`"
        class="grid text-sm overflow-hidden transition-all duration-300 ease-in-out ml-2"
        :class="accordionOpen ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'">
        <div class="overflow-hidden">
            <p>
                <slot />
            </p>
        </div>
    </div>
</template>
<script>
export default {
    data() {
        return {
            accordionOpen: this.active,
        }
    },
    props: {
        title: String,
        id: Number,
        active: Boolean,
        icon: String,
    },
    components: {
    }
}
</script>
