<script>
export default {
    props: {
        successMessage: { type: String },
        errorMessage: { type: String },
    }
}
</script>

<template>
    <div class="result-messages-div">
        <h4 v-if="successMessage != null" class="success">{{ successMessage }}</h4>
        <p v-if="errorMessage != null" class="error">{{ errorMessage }}</p>
    </div>
</template>

<style>
.result-messages-div {
    margin-top: 1rem;
    margin-bottom: 1rem;
    padding: 0.75rem;
    font-weight: 700;
    font-size: 1.125rem;
    line-height: 1.75rem;
}
</style>