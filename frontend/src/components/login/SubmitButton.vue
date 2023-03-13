<script>
export default {
    props: {
        disabled: {
            type: Boolean, default: true
        }
    }
}
</script>

<template>
    <button type="button" name="submit" class="text button" :disabled="disabled">
        <slot name="submit"></slot>
    </button>
</template>

<style>
.text {
    color: var(--login-form-labels);
    font-weight: 700;
    font-size: 1.125rem;
    line-height: 1.75rem;
}

.button {
    margin-top: 10px;
    padding: 7px 14px;
    cursor: pointer;
    border-radius: 0.375rem;
}

.button:disabled,
.button[disabled] {
    border: 1px solid #999999;
    background-color: #777395;
    color: #ffffff;
    cursor: default;
}

.button:hover:enabled {
    color: var(--text-accent);
    background-color: var(--header);
}
</style>