<script>

export default {
    props: {
        content: {
            type: String, required: true
        }
    }
}
</script>

<template>
    <td class="postCard">{{ content }}</td>
</template>

<style>
.postCard {
    background-color: var(--form-input);
    border-radius: 0.375rem;
    padding: 2%;
    vertical-align: top;

    font-size: 1rem;
    line-height: 1.5rem;
    font-weight: 400;
}
</style>