<script setup lang="ts">
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import type { Hotel } from "../../types";

const props = defineProps<{ initialHotels: Hotel[]; initialSearch: string; providerError: string | null }>();
const hotels = ref(props.initialHotels),
  search = ref(props.initialSearch || ""),
  city = ref(""),
  min = ref<number | null>(null),
  max = ref<number | null>(null);
const loading = ref(false),
  error = ref(props.providerError || ""),
  success = ref(""),
  selected = ref<Hotel | null>(null),
  modal = ref(false),
  saving = ref(false);
const form = ref({ guest_name: "", guest_email: "", check_in: "", check_out: "", guests: 1, notes: "" });
const cities = computed(() => [...new Set(hotels.value.map((h) => h.city))]);
const today = new Date().toISOString().slice(0, 10);
async function load() {
  loading.value = true;
  error.value = "";
  try {
    const r = await axios.get("/api/hotels", {
      params: {
        search: search.value || undefined,
        city: city.value || undefined,
        min_price: min.value ?? undefined,
        max_price: max.value ?? undefined,
        per_page: 30,
      },
    });
    hotels.value = r.data.data;
  } catch (e: any) {
    error.value = e.response?.data?.message || "Unable to load hotels.";
  } finally {
    loading.value = false;
  }
}
function book(h: Hotel) {
  selected.value = h;
  modal.value = true;
  success.value = "";
}
async function submit() {
  if (!selected.value) return;
  saving.value = true;
  error.value = "";
  try {
    const r = await axios.post("/api/bookings", { hotel_id: selected.value.id, ...form.value });
    success.value = `Booking #${r.data.data.id} confirmed. Total ৳${r.data.data.total_price}`;
    modal.value = false;
    form.value = { guest_name: "", guest_email: "", check_in: "", check_out: "", guests: 1, notes: "" };
  } catch (e: any) {
    error.value =
      e.response?.data?.message ||
      Object.values(e.response?.data?.errors || {})
        .flat()
        .join(" ") ||
      "Booking failed.";
  } finally {
    saving.value = false;
  }
}
</script>
<template>
  <main class="min-h-screen bg-slate-50">
    <header class="border-b bg-white">
      <div class="mx-auto flex max-w-7xl justify-between px-5 py-5">
        <a href="/" class="text-2xl font-bold text-blue-700">TravelDesk</a
        ><button @click="router.visit('/bookings')" class="rounded-lg border px-4 py-2">My bookings</button>
      </div>
    </header>
    <section class="mx-auto max-w-7xl px-5 py-10">
      <p class="font-semibold uppercase tracking-wider text-blue-700">Find your next stay</p>
      <h1 class="mt-2 text-4xl font-bold">Explore places to stay</h1>
      <p class="mt-3 text-slate-600">Listings are loaded from an external API and normalized by Laravel.</p>
      <div class="mt-7 grid gap-3 rounded-xl border bg-white p-4 md:grid-cols-4">
        <input v-model="search" @keyup.enter="load" placeholder="Search hotels" class="field md:col-span-2" /><select
          v-model="city"
          class="field"
        >
          <option value="">All cities</option>
          <option v-for="c in cities" :key="c">{{ c }}</option></select
        ><button @click="load" class="rounded-lg bg-blue-700 p-3 font-semibold text-white">
          {{ loading ? "Searching..." : "Search" }}</button
        ><input v-model.number="min" type="number" placeholder="Min price" class="field" /><input
          v-model.number="max"
          type="number"
          placeholder="Max price"
          class="field"
        /><button
          @click="
            search = '';
            city = '';
            min = null;
            max = null;
            load();
          "
          class="text-left text-sm text-blue-700"
        >
          Clear filters
        </button>
      </div>
      <p v-if="error" class="mt-5 rounded bg-red-50 p-4 text-red-700">{{ error }}</p>
      <p v-if="success" class="mt-5 rounded bg-green-50 p-4 text-green-700">{{ success }}</p>
      <div v-if="loading" class="py-16 text-center">Loading...</div>
      <div v-else class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <article v-for="h in hotels" :key="h.id" class="overflow-hidden rounded-xl border bg-white">
          <img :src="h.image" class="h-48 w-full object-cover" />
          <div class="p-5">
            <div class="flex justify-between">
              <div>
                <h2 class="font-bold">{{ h.name }}</h2>
                <p class="text-sm text-slate-500">{{ h.city }}</p>
              </div>
              <span>★ {{ h.rating }}</span>
            </div>
            <p class="mt-3 line-clamp-3 text-sm text-slate-600">{{ h.description }}</p>
            <div class="mt-5 flex items-center justify-between">
              <b>৳{{ h.price_per_night.toLocaleString() }} <small class="font-normal text-slate-500">/ night</small></b
              ><button @click="book(h)" class="rounded bg-blue-700 px-4 py-2 text-white">Book now</button>
            </div>
          </div>
        </article>
      </div>
      <p v-if="!loading && !hotels.length" class="py-10 text-center">No hotels found.</p>
    </section>
    <div v-if="modal && selected" class="fixed inset-0 z-20 flex items-center justify-center bg-black/50 p-4">
      <form @submit.prevent="submit" class="w-full max-w-xl rounded-xl bg-white p-6">
        <div class="flex justify-between">
          <h2 class="text-xl font-bold">Book {{ selected.name }}</h2>
          <button type="button" @click="modal = false">×</button>
        </div>
        <div class="mt-5 grid gap-3 sm:grid-cols-2">
          <label class="form-label">Guest name<input v-model="form.guest_name" required class="field mt-1" /></label
          ><label class="form-label"
            >Email<input v-model="form.guest_email" type="email" required class="field mt-1" /></label
          ><label class="form-label"
            >Check-in<input v-model="form.check_in" :min="today" type="date" required class="field mt-1" /></label
          ><label class="form-label"
            >Check-out<input
              v-model="form.check_out"
              :min="form.check_in || today"
              type="date"
              required
              class="field mt-1" /></label
          ><label class="form-label"
            >Guests<input v-model.number="form.guests" type="number" min="1" max="12" class="field mt-1" /></label
          ><label class="form-label sm:col-span-2"
            >Notes<textarea v-model="form.notes" class="field mt-1"></textarea>
          </label>
        </div>
        <div class="mt-5 flex justify-end gap-3">
          <button type="button" @click="modal = false" class="rounded border px-4 py-2">Close</button
          ><button :disabled="saving" class="rounded bg-blue-700 px-4 py-2 text-white">
            {{ saving ? "Saving..." : "Confirm booking" }}
          </button>
        </div>
      </form>
    </div>
  </main>
</template>
