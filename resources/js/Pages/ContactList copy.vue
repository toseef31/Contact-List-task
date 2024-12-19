<template>
    <div class="flex justify-center">
      <!-- Client List -->
      <div class="h-screen w-full max-w-lg bg-white rounded-lg shadow-md overflow-auto mt-5" ref="listContainer">
        <!-- Group Header -->
        <h2 class="text-2xl font-semibold p-4 border-b">Customers</h2>
        <div v-for="(group, index) in clientsGrouped" :key="index" :ref="`group-${group.letter}`">

          <div class="px-4 py-2 bg-gray-100 font-bold sticky top-0 z-10">
            {{ group.letter }}
          </div>
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

  <script>
  import axios from "axios";
  import Alphabet from '../Components/AlphabetBar.vue';

 export default {
    components: {
    Alphabet
  },
  data() {
    return {
      alphabet: "ABCDEFGHIJKLMNOPQRSTUVWXYZ".split(""),
      clientsGrouped: [],
      loading: false,
      loadedLetters: [],
    };
  },
  methods: {
    async fetchClients(letter) {
        if (this.loadedLetters.includes(letter) && this.loading) {
            return;
        }

        this.loading = true;
        try {
            const response = await axios.get(`/api/clients?letter=${letter}`);
            const clients = response.data.clients.map(client => ({
            ...client,
            color: this.getRandomColor(),
            }));

            const existingGroupIndex = this.clientsGrouped.findIndex(group => group.letter === letter);

            if (existingGroupIndex !== -1) {
            this.clientsGrouped[existingGroupIndex].clients.push(...clients);
            } else {
            this.clientsGrouped.push({ letter, clients });
            }

            this.clientsGrouped.sort((a, b) => a.letter.localeCompare(b.letter));

            const currentLetterIndex = this.alphabet.indexOf(letter);
            for (let i = currentLetterIndex - 1; i >= 0; i--) {
                const prevLetter = this.alphabet[i];
                if (!this.loadedLetters.includes(prevLetter)) {
                    await this.fetchClients(prevLetter);
                }
            }

            this.loadedLetters.push(letter);
        } catch (error) {
            console.error("Error fetching clients:", error);
        } finally {
            this.loading = false;
        }
    },


    scrollToLetter(letter) {
      if (!this.loadedLetters.includes(letter)) {
        this.fetchClients(letter).then(() => {
          const currentLetterIndex = this.alphabet.indexOf(letter);
          const lettersToFetch = this.alphabet.slice(0, currentLetterIndex + 1);

          const newLetters = lettersToFetch.filter(
            prevLetter => !this.loadedLetters.includes(prevLetter)
          );

          if (newLetters.length > 0) {
            const fetchPromises = newLetters.map(this.fetchClients);
            Promise.all(fetchPromises).then(() => {
              this.scrollToGroup(letter);
            }).catch(error => console.error("Error fetching letters:", error));
          } else {
            this.scrollToGroup(letter);
          }
        });
      } else {
        this.scrollToGroup(letter);
      }
    },
    scrollToGroup(letter) {
      const targetGroup = this.$refs[`group-${letter}`];
      if (targetGroup && targetGroup[0]) {
        targetGroup[0].scrollIntoView({ behavior: "smooth" });
      }
    },
    handleScroll() {
      const container = this.$refs.listContainer;
      const scrollTop = container.scrollTop;
      const scrollHeight = container.scrollHeight;
      const clientHeight = container.clientHeight;

      if (scrollTop + clientHeight >= scrollHeight - 100) {
        const lastLoadedLetter = this.loadedLetters[this.loadedLetters.length - 1];
        const nextLetterIndex = this.alphabet.indexOf(lastLoadedLetter) + 1;

        if (nextLetterIndex < this.alphabet.length) {
          this.fetchClients(this.alphabet[nextLetterIndex]);
        }
      } else if (scrollTop <= 100) {
        const firstLoadedLetter = this.loadedLetters[0];
        const prevLetterIndex = this.alphabet.indexOf(firstLoadedLetter) - 1;

        if (prevLetterIndex >= 0) {
          this.fetchClients(this.alphabet[prevLetterIndex]);
        }
      }
    },
    getRandomColor() {
      const colors = ["#60A5FA", "#4B5563", "#FACC15", "#3B82F6", "#38BDF8", "#A3E635", "#F472B6"];
      return colors[Math.floor(Math.random() * colors.length)];
    },
  },
  mounted() {
    this.fetchClients("A");
    this.$refs.listContainer.addEventListener("scroll", this.handleScroll);
  },
  beforeUnmount() {
    this.$refs.listContainer.removeEventListener("scroll", this.handleScroll);
  },
};
</script>
