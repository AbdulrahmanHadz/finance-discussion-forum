<script>
export default {
    props: {
        error: { type: String }
    }
}
</script>

<template>
    <p class="error w-52 text-rose-600 font-bold break-word text-xs pt-2">-
        {{
        error
        }}
    </p>
</template>