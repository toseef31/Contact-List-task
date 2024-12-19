<template>
    <div class="h-screen overflow-auto">
      <ul class="flex flex-col items-center space-y-2">
        <li
          v-for="letter in alphabet"
          :key="letter"
          class="cursor-pointer text-gray-700 hover:text-blue-500 font-semibold"
          @click="$emit('letter-clicked', letter)"
        >
          {{ letter }}
        </li>
      </ul>
    </div>
  </template>

  <script>
  export default {
    props: {
      alphabet: {
        type: Array,
        required: true
      }
    }
  };
  </script>

  <style scoped>
  /* Add any necessary styles */
  </style>
