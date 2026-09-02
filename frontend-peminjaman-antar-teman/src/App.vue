<template>
  <div id="app-wrapper">
    <HomeView />
  </div>
</template>

<script>
import HomeView from './views/Home.vue'

export default {
  name: 'App',
  components: {
    HomeView
  }
}
</script>

<style>
/* CSS Reset dasar */
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