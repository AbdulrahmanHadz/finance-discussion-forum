<script setup>
import NavBar from '../components/navbar/Nav.vue'
import { headerKey } from '../javascript/keys.js';
</script>

<template>
    <NavBar :key="headerKey" />
</template>