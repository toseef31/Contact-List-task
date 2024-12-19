<script setup>
    import { ref, onMounted, onBeforeUnmount } from 'vue';  // Import Vue Composition API functions and Axios
    import axios from 'axios';  // Import Axios for API requests
    import Alphabet from '@/components/AlphabetBar.vue';  // Import AlphabetBar component using alias
    import _ from 'lodash';
    // Define reactive references for alphabet letters, grouped clients, loading state, loaded letters, and list container
    const alphabet = ref("ABCDEFGHIJKLMNOPQRSTUVWXYZ".split(""));  // List of letters
    const clientsGrouped = ref([]);  // Grouped clients
    const loading = ref(false);  // Loading state
    const loadedLetters = ref([]);  // Letters already loaded
    const listContainer = ref(null);  // Reference to the list container element

    // Function to fetch clients based on the starting letter
    const fetchClients = async (letter) => {
        if (loadedLetters.value.includes(letter) && loading.value) {
            return;  // Skip fetch if letter is already loaded and fetching is in progress
        }

        loading.value = true;  // Set loading state
        try {
            const response = await axios.get(`/api/clients?letter=${letter}`);  // Fetch clients
            const clients = response.data.clients.map(client => ({
                ...client,
                color: getRandomColor(),  // Assign random color to each client
            }));

            const existingGroupIndex = clientsGrouped.value.findIndex(group => group.letter === letter);  // Check if group for the letter exists

            if (existingGroupIndex !== -1) {
                clientsGrouped.value[existingGroupIndex].clients.push(...clients);  // Add clients to existing group
            } else {
                clientsGrouped.value.push({ letter, clients });  // Create new group for letter
            }

            clientsGrouped.value.sort((a, b) => a.letter.localeCompare(b.letter));  // Sort groups alphabetically

            // Fetch previous letters if not loaded
            const currentLetterIndex = alphabet.value.indexOf(letter);
            for (let i = currentLetterIndex - 1; i >= 0; i--) {
                const prevLetter = alphabet.value[i];
                if (!loadedLetters.value.includes(prevLetter)) {
                    await fetchClients(prevLetter);
                }
            }

            loadedLetters.value.push(letter);  // Mark letter as loaded
        } catch (error) {
            console.error("Error fetching clients:", error);  // Handle fetch error
        } finally {
            loading.value = false;  // Reset loading state
        }
    };

    // Function to scroll to a specific letter group
    const scrollToLetter = async (letter) => {
        if (!loadedLetters.value.includes(letter)) {
            await fetchClients(letter);  // Fetch clients for the given letter
            scrollToGroup(letter);  // Scroll to the letter group
        } else {
            scrollToGroup(letter);  // Scroll to the letter group if already loaded
        }
    };

    // Function to scroll to the group of clients based on the letter
    const scrollToGroup = (letter) => {
        const targetGroup = ref(`group-${letter}`).value;  // Construct ref for group
        const targetElement = targetGroup ? document.querySelector(`[data-ref="${targetGroup}"]`) : null;  // Get DOM element

        if (targetElement) {
            try {
                targetElement.scrollIntoView({ behavior: "smooth" });  // Smooth scroll to the group
            } catch (error) {
                console.error("Error scrolling to group:", error);  // Handle scrolling error
            }
        } else {
            console.warn(`Target element for group-${letter} not found.`);  // Warn if group element not found
        }
    };

    // Function to handle scroll events
    const handleScroll = _.debounce(async () => {
    const container = listContainer.value;  // Get list container
    const scrollTop = container.scrollTop;  // Scroll position
    const scrollHeight = container.scrollHeight;  // Total scrollable height
    const clientHeight = container.clientHeight;  // Visible height of the container

    if (scrollTop + clientHeight >= scrollHeight - 100) {
        // Load next letter group if near the bottom
        const lastLoadedLetter = loadedLetters.value[loadedLetters.value.length - 1];
        const nextLetterIndex = alphabet.value.indexOf(lastLoadedLetter) + 1;

        if (nextLetterIndex < alphabet.value.length) {
            await fetchClients(alphabet.value[nextLetterIndex]);
        }
    } else if (scrollTop <= 100) {
        // Load previous letter group if near the top
        const firstLoadedLetter = loadedLetters.value[0];
        const prevLetterIndex = alphabet.value.indexOf(firstLoadedLetter) - 1;

        if (prevLetterIndex >= 0) {
            await fetchClients(alphabet.value[prevLetterIndex]);
        }
    }
}, 200);  // Debounce delay (in milliseconds)



    // Function to generate random color for clients
    const getRandomColor = () => {
        const colors = ["#60A5FA", "#4B5563", "#FACC15", "#3B82F6", "#38BDF8", "#A3E635", "#F472B6"];
        return colors[Math.floor(Math.random() * colors.length)];
    };

    // Load clients for letter "A" on component mount and attach scroll event listener
    onMounted(() => {
        fetchClients("A");
        if (listContainer.value) {
            listContainer.value.addEventListener("scroll", handleScroll);
        }
    });

    // Remove scroll event listener when component is unmounted
    onBeforeUnmount(() => {
        if (listContainer.value) {
            listContainer.value.removeEventListener("scroll", handleScroll);
        }
    });
</script>

<template>
    <div class="flex justify-center">
      <!-- Client List -->
      <div ref="listContainer" class="h-screen w-full max-w-lg bg-white rounded-lg shadow-md overflow-auto mt-5">
        <!-- Group Header -->
        <h2 class="text-2xl font-semibold p-4 border-b">Customers</h2>
        <div v-for="(group, index) in clientsGrouped" :key="index" :data-ref="`group-${group.letter}`">
          <div class="px-4 py-2 bg-gray-100 font-bold sticky top-0 z-10">{{ group.letter }}</div>
          <!-- Client Items -->
          <div
            v-for="client in group.clients"
            :key="client.id"
            class="flex items-center px-4 py-2 border-b"
          >
            <div
              class="w-10 h-10 flex items-center justify-center rounded-full text-white text-sm font-bold"
              :style="{ backgroundColor: client.color }"
            >
              {{ client.initials }}
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium">{{ client.name }}</p>
              <p class="text-xs text-gray-500">{{ client.phone_number }}</p>
            </div>
          </div>
        </div>
      </div>
      <!-- Alphabet Navigation Bar -->
      <Alphabet :alphabet="alphabet" @letter-clicked="scrollToLetter" />
    </div>
  </template>

  <style scoped>
  /* Add custom styles if needed */
  </style>
