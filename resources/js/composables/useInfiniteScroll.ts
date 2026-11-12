
import { nextTick, onMounted, ref, type Ref } from "vue";
import axios from "axios";

interface CursorPaginationResponse<T> {
    data: T[];
    next_cursor: string | null;
    prev_cursor: string | null;
    per_page: number;
}

interface InfiniteScrollOptions {
    params?: Record<string, any>;
    threshold?: number;
}

export function useInfiniteScroll<T>(
    endpoint: string,
    options: InfiniteScrollOptions = {},
) {
    const items = ref<T[]>([]) as Ref<T[]>;
    const loading = ref(false);
    const loadingMore = ref(false);
    const error = ref("");

    const hasMore = ref(true);
    const nextCursor = ref<string | null>(null);

    const scrollContainer = ref<HTMLElement | null>(null);

    const threshold = options.threshold ?? 100;

    async function loadMore() {
        if (loading.value || loadingMore.value || !hasMore.value) {
            return;
        }

        const initial = items.value.length === 0;

        if (initial) {
            loading.value = true;
        } else {
            loadingMore.value = true;
        }

        error.value = "";

        let loadedSuccessfully = false;

        try {
            const response = await axios.get(endpoint, {
                params: {
                    ...options.params,
                    ...(nextCursor.value
                        ? { cursor: nextCursor.value }
                        : {}),
                },
            });

            const pagination: CursorPaginationResponse<T> =
                response.data.data;

            items.value.push(...pagination.data);

            nextCursor.value = pagination.next_cursor;
            hasMore.value = !!pagination.next_cursor;

            loadedSuccessfully = true;
        } catch (e: any) {
            error.value =
                e.response?.data?.message ||
                "Could not load data.";
        } finally {
            loading.value = false;
            loadingMore.value = false;
        }

        // If the container isn't filled yet, automatically
        // fetch another page so scrolling becomes possible.
        if (loadedSuccessfully && hasMore.value) {
            await nextTick();

            const element = scrollContainer.value;

            if (
                element &&
                element.scrollHeight <= element.clientHeight + threshold
            ) {
                loadMore();
            }
        }
    }

    function handleScroll() {
        const element = scrollContainer.value;

        if (!element) return;

        const nearBottom =
            element.scrollTop + element.clientHeight >=
            element.scrollHeight - threshold;

        if (nearBottom) {
            loadMore();
        }
    }

    function reset() {
        items.value = [];
        nextCursor.value = null;
        hasMore.value = true;
        error.value = "";

        loadMore();
    }

    onMounted(loadMore);

    return {
        items,
        loading,
        loadingMore,
        error,
        hasMore,
        nextCursor,
        scrollContainer,
        handleScroll,
        loadMore,
        reset,
    };
}