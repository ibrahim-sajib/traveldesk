
<script setup lang="ts">
import { useInfiniteScroll } from "../../composables/useInfiniteScroll";
import axios from "axios";
import type { Booking } from "../../types";

const {
    items: bookings,
    loading,
    loadingMore,
    error,
    hasMore,
    scrollContainer,
    handleScroll,
    reset,
} = useInfiniteScroll<Booking>("/api/bookings", {
    threshold: 100,
});

async function cancel(b: Booking) {
    if (!confirm(`Cancel booking #${b.id}?`)) return;

    try {
        await axios.patch(`/api/bookings/${b.id}/cancel`);
        reset();
    } catch (e: any) {
        error.value =
            e.response?.data?.message || "Cancellation failed";
    }
}
</script>

<template>
    <main class="min-h-screen bg-slate-50">
        <header class="border-b bg-white">
            <div class="mx-auto flex max-w-6xl justify-between px-5 py-5">
                <a href="/" class="text-2xl font-bold text-blue-700">
                    TravelDesk
                </a>
                <a href="/" class="text-blue-700">← Browse hotels</a>
            </div>
        </header>

        <section class="mx-auto max-w-6xl px-5 py-10">
            <h1 class="text-3xl font-bold">Bookings</h1>

            <p class="mt-2 text-slate-600">
                Reservations stored in your database.
            </p>

            <p v-if="error" class="mt-4 text-red-700">
                {{ error }}
            </p>

            <p v-if="loading" class="py-10">
                Loading...
            </p>

            <div
                v-else-if="bookings.length"
                ref="scrollContainer"
                @scroll="handleScroll"
                class="mt-6 max-h-[600px] min-h-[300px] overflow-y-auto divide-y rounded-xl border bg-white"
            >
                <div
                    v-for="b in bookings"
                    :key="b.id"
                    class="flex flex-col justify-between gap-4 p-5 sm:flex-row sm:items-center"
                >
                    <div>
                        <b>#{{ b.id }} · {{ b.hotel_name }}</b>

                        <p class="text-sm text-slate-600">
                            {{ b.guest_name }} · {{ b.guest_email }}
                        </p>

                        <p class="text-sm text-slate-500">
                            {{ b.check_in }} → {{ b.check_out }}
                            · {{ b.guests }} guest(s)
                        </p>

                        <b>৳{{ b.total_price }}</b>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs uppercase">
                            {{ b.status }}
                        </span>

                        <button
                            v-if="b.status === 'confirmed'"
                            @click="cancel(b)"
                            class="rounded border border-red-200 px-3 py-2 text-sm text-red-700"
                        >
                            Cancel
                        </button>
                    </div>
                </div>

                <div
                    v-if="loadingMore"
                    class="p-5 text-center text-sm text-slate-500"
                >
                    Loading more bookings...
                </div>

                <div
                    v-if="!hasMore"
                    class="p-5 text-center text-sm text-slate-500"
                >
                    You've reached the end.
                </div>
            </div>

            <p
                v-else-if="!loading"
                class="mt-6 rounded border border-dashed bg-white p-10 text-center"
            >
                No bookings yet.
                <a href="/" class="text-blue-700">Find a hotel</a>.
            </p>
        </section>
    </main>
</template>