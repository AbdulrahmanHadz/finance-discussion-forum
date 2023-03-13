<script>
import { RouterView } from 'vue-router'
import Nav from './components/navbar/Nav.vue'
import HomePageVue from './views/HomePage.vue';
import { tagsData } from './javascript/tags';

export default {
  components: { Nav, HomePageVue },
  created() {
    tagsData()
  }
}

</script>

<template>
  <!-- <div>
    <table class="contentTable">
      <tr>
        <td class="headerRow">
          <RouterView class="headerDiv" name="Header" />
        </td>
      </tr>
      <tr>
        <td>
          <RouterView name="MainContent" />
        </td>
      </tr>
    </table>
  </div> -->
  <div class="mainDiv">
    <div class="headerRow">
      <RouterView class="headerDiv" name="Header" />
    </div>
    <div class="mainContent">
      <RouterView name="MainContent" />
    </div>
  </div>
</template>

<style>
.mainContent {
  /* margin-top: 15vh; */
  /* margin-top: 1%; */
  /* width: 90%;
  height: 100%; */

  height: 100%;
  justify-content: center;
  align-items: center;
  margin: 0 auto;

  /* IE*/
  /* border: 3px solid black; */

  /* margin: 0 auto; */
  /* padding: 4px; */
  /* border-width: 2px; */
  /* border-color: black; */
}

.mainDiv {
  /* height: 100vh; */
  width: 100%;
  /* display: flex; */
  display: table;
  justify-content: center;
  align-items: center;
  flex-direction: column;
}

.headerRow {
  padding-top: 1.5rem;
  /* padding-bottom: 1.5rem; */
}

.headerDiv {
  width: 90%;
  margin: 0 auto;
}

.contentTable {
  width: 100%;
  height: 100%;
}
</style>
