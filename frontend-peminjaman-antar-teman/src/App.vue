<template>
  <div id="app-wrapper">
    <router-view />
    <!-- <Router-view /> -->
    <!-- <RouterLink to="/login">Login</RouterLink> -->
  </div>
</template>

<script>
export default {
  name: 'App'
}
</script>

<style>
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  font-family: sans-serif;
  background-color: #FDFBF7;
}
</style>