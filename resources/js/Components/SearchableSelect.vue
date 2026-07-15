<template>
  <div class="relative w-full" ref="container">
    <div 
      @click="!disabled && toggle()"
      class="w-full bg-white border px-3 py-2 text-left cursor-default sm:text-sm flex justify-between items-center transition-colors"
      :class="{ 
        'ring-1 ring-blue-500 border-blue-500': isOpen && !disabled, 
        'border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm rounded-md': !disabled,
        'bg-gray-100 text-gray-500 border-gray-200 cursor-not-allowed rounded': disabled 
      }"
    >
      <span class="block truncate" v-if="selectedOption">{{ selectedOption[labelKey] }}</span>
      <span class="block truncate" :class="disabled ? 'text-gray-400' : 'text-gray-500'" v-else>{{ placeholder }}</span>
      <span class="pointer-events-none flex items-center">
        <svg class="h-5 w-5" :class="disabled ? 'text-gray-300' : 'text-gray-400'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
          <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
      </span>
    </div>

    <div v-if="isOpen" class="absolute z-50 mt-1 w-full bg-white shadow-xl max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
      <div class="px-2 pb-2 sticky top-0 bg-white pt-1 z-10">
        <input 
          type="text" 
          v-model="search" 
          @click.stop
          class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
          placeholder="ស្វែងរក..."
          ref="searchInput"
        />
      </div>
      <ul tabindex="-1" role="listbox">
        <li
          @click="selectOption(null)"
          class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-blue-50 text-gray-500"
        >
          <span class="block truncate font-normal">
            -- មិនជ្រើសរើស --
          </span>
        </li>
        <li 
          v-for="option in filteredOptions" 
          :key="option[valueKey]"
          @click="selectOption(option)"
          class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-blue-600 hover:text-white group"
        >
          <span class="block truncate font-normal" :class="{ 'font-semibold': isSelected(option) }">
            {{ option[labelKey] }}
          </span>
          <span v-if="isSelected(option)" class="absolute inset-y-0 right-0 flex items-center pr-4 text-blue-600 group-hover:text-white">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
          </span>
        </li>
        <li v-if="filteredOptions.length === 0" class="cursor-default select-none relative py-2 pl-3 pr-9 text-gray-500 text-sm">
          មិនមានទិន្នន័យ
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps({
  disabled: {
    type: Boolean,
    default: false
  },
  modelValue: {
    type: [String, Number, null],
    default: null
  },
  options: {
    type: Array,
    required: true
  },
  valueKey: {
    type: String,
    default: 'id'
  },
  labelKey: {
    type: String,
    default: 'name'
  },
  placeholder: {
    type: String,
    default: '-- ជ្រើសរើស --'
  }
});

const emit = defineEmits(['update:modelValue', 'change']);

const isOpen = ref(false);
const search = ref('');
const container = ref(null);
const searchInput = ref(null);

const filteredOptions = computed(() => {
  if (!search.value) return props.options;
  const lowerSearch = search.value.toLowerCase();
  return props.options.filter(opt => 
    String(opt[props.labelKey]).toLowerCase().includes(lowerSearch)
  );
});

const selectedOption = computed(() => {
  if (props.modelValue === null || props.modelValue === '') return null;
  return props.options.find(opt => String(opt[props.valueKey]) === String(props.modelValue));
});

const isSelected = (option) => {
  return String(option[props.valueKey]) === String(props.modelValue);
};

const toggle = () => {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    search.value = '';
    nextTick(() => {
      if (searchInput.value) searchInput.value.focus();
    });
  }
};

const selectOption = (option) => {
  const value = option ? option[props.valueKey] : null;
  emit('update:modelValue', value);
  emit('change', value);
  isOpen.value = false;
};

const closeOnOutsideClick = (e) => {
  if (container.value && !container.value.contains(e.target)) {
    isOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', closeOnOutsideClick);
});

onUnmounted(() => {
  document.removeEventListener('click', closeOnOutsideClick);
});
</script>
