<script>
import NavLink from './NavLink.vue';
import { isLoggedIn, loggedInUsername } from '../../javascript/loggedin.js'

export default {
    components: { NavLink },
    methods: {
        isLoggedIn,
        loggedInUsername
    }
}

</script>

<template>
    <nav class="nav">
        <div class="navlinks">
            <NavLink to="/" name="Home" />
            <NavLink to="/search" name="Search" />
            <NavLink to="/post/new" name="Add Post" />
            <NavLink to="/tag/new" name="Add Tag" />
        </div>
        <div class="navlinks" v-if="!isLoggedIn()">
            <NavLink to="/register" name="Register" />
            <NavLink to="/login" name="Login" />
        </div>
        <div class="navlinks" v-if="isLoggedIn()">
            <NavLink to="/me" :name="loggedInUsername()" />
            <NavLink to="/logout" name="Logout" />
        </div>
    </nav>
</template>


<style>
.nav {
    display: flex;
    justify-content: space-between;
    padding-left: 7px;
    padding-right: 7px;
    padding-top: 13px;
    padding-bottom: 13px;
    background-color: var(--header);
    border-radius: 0.375rem;
}

.navlinks {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
}
</style>