<script setup>
import { ref } from 'vue';
import { useForm } from 'laravel-precognition-vue-inertia';
import { TrashIcon, PencilIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConditionalBadge from '@/Components/ConditionalBadge.vue';
import DangerButton from '@/Components/DangerButton.vue';
import DialogModal from '@/Components/DialogModal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    users: Object,
});

console.log(props.users);

const showModal = ref(false);

const modal = ref({
  title: '',
  content: '',
  submit: '',
  action: '',
});

const form = useForm({
    id: '',
});

const modalProcess = (action) => {
  if (action == 'delete') {
    deleteUser();
  } if (action == 'restore') {
    restoreUser();
  }
};

const confirmUserDeletion = (user) => {
    form.id = user.id;

    modal.value.title = 'Delete Account';
    modal.value.content = 'Are you sure you want to delete this account? Once this account is deleted, all of its resources and data will be permanently deleted.';
    modal.value.submit = 'Delete Account';
    modal.value.action = 'delete';

    showModal.value = true;
};

const deleteUser = () => {
    form.delete(route('users.destroy', form.id), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: (err) => alert(err),
        onFinish: () => form.reset(),
    });
};

const confirmUserRestoration = (user) => {
    form.id = user.id;

    modal.value.title = 'Restore Account';
    modal.value.content = 'Are you sure you want to restore this account?';
    modal.value.submit = 'Restore Account';
    modal.value.action = 'restore';

    showModal.value = true;
};

const restoreUser = () => {
    form.patch(route('users.restore', form.id), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: (err) => alert(err),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    showModal.value = false;

    form.reset();
};
</script>

<template>
  <AppLayout title="Users">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        List Users
      </h2>
    </template>

    <div>
      <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <div class="px-4 py-5 bg-white dark:bg-gray-800 sm:p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
          Header of the table
        </div>
        <div class="px-4 py-5 bg-white dark:bg-gray-800 sm:px-6 shadow overflow-x-auto">
          <table class="border-collapse table-auto w-full text-sm">
            <thead>
              <tr>
                <th class="border-b border-slate-200 dark:border-slate-600 p-4 pl-8 text-slate-500 dark:text-slate-400">
                  #
                </th>
                <th class="border-b border-slate-200 dark:border-slate-600 p-4 pl-8 text-slate-500 dark:text-slate-400">
                  Photo
                </th>
                <th class="border-b border-slate-200 dark:border-slate-600 p-4 pl-8 text-slate-500 dark:text-slate-400">
                  Name
                </th>
                <th class="border-b border-slate-200 dark:border-slate-600 p-4 pl-8 text-slate-500 dark:text-slate-400">
                  Username
                </th>
                <th
                  class="border-b border-slate-200 dark:border-slate-600 p-4 pl-8 text-slate-500 dark:text-slate-400"
                  :colspan="$page.props.jetstream.hasEmailVerification ? 2 : 1"
                >
                  Email
                </th>
                <th
                  class="border-b border-slate-200 dark:border-slate-600 p-4 pl-8 text-slate-500 dark:text-slate-400"
                  :colspan="$page.props.jetstream.hasEmailVerification ? 2 : 1"
                >
                  Phone
                </th>
                <th
                  class="border-b border-slate-200 dark:border-slate-600 p-4 pl-8 text-slate-500 dark:text-slate-400"
                >
                  Status
                </th>
                <th class="border-b border-slate-200 dark:border-slate-600 p-4 pl-8 text-slate-500 dark:text-slate-400">
                  Action
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="user in users.data"
                :key="user.id"
                class="even:bg-slate-100 dark:even:bg-slate-700"
              >
                <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400 text-center">
                  {{ user.id }}
                </td>
                <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400">
                  <div class="flex justify-center">
                    <img
                      :src="user.profile_photo_url"
                      :alt="user.name"
                      class="rounded-full h-10 w-10 object-cover"
                    >
                  </div>
                </td>
                <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400">
                  {{ user.name }}
                </td>
                <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400">
                  {{ user.username }}
                </td>
                <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 pr-0 text-slate-500 dark:text-slate-400">
                  {{ user.email }}
                </td>
                <td
                  v-if="$page.props.jetstream.hasEmailVerification"
                  class="border-b border-slate-100 dark:border-slate-700 p-4 pl-0 text-slate-500 dark:text-slate-400"
                >
                  <ConditionalBadge
                    :condition="user.email_verified_at !== null"
                    value-true="Verified"
                    value-false="Unverified"
                  />
                </td>
                <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 pr-0 text-slate-500 dark:text-slate-400">
                  +62{{ user.phone }}
                </td>
                <td
                  v-if="$page.props.jetstream.hasPhoneVerification"
                  class="border-b border-slate-100 dark:border-slate-700 p-4 pl-0 text-slate-500 dark:text-slate-400"
                >
                  <ConditionalBadge
                    :condition="user.phone_verified_at !== null"
                    value-true="Verified"
                    value-false="Unverified"
                  />
                </td>
                <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400">
                  <ConditionalBadge
                    :condition="user.deleted_at === null"
                    value-true="Active"
                    value-false="Deleted"
                    class-false="badge-error"
                  />
                </td>
                <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400 text-center">
                  <div class="flex justify-center gap-4">
                    <template v-if="user.deleted_at === null">
                      <div
                        class="tooltip"
                        data-tip="Delete"
                      >
                        <a
                          href="#"
                          @click.prevent="confirmUserDeletion(user)"
                        >
                          <TrashIcon class="w-6 h-6 stroke-current hover:stroke-error" />
                        </a>
                      </div>
                      <div
                        class="tooltip"
                        data-tip="Edit"
                      >
                        <a
                          :href="route('users.edit', user.id)"
                        >
                          <PencilIcon class="w-6 h-6 stroke-current hover:stroke-warning" />
                        </a>
                      </div>
                    </template>

                    <template v-else>
                      <div
                        class="tooltip"
                        data-tip="Restore"
                      >
                        <a
                          href="#"
                          @click.prevent="confirmUserRestoration(user)"
                        >
                          <ArrowPathIcon class="w-6 h-6 stroke-current hover:stroke-primary" />
                        </a>
                      </div>
                    </template>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="flex items-center justify-center px-4 py-5 bg-gray-50 dark:bg-gray-800 text-center sm:px-6 shadow sm:rounded-bl-md sm:rounded-br-md">
          Footer of the table
        </div>
      </div>
    </div>

    <!-- Reusable Modal -->
    <DialogModal
      :show="showModal"
      @close="closeModal"
    >
      <template #title>
        {{ modal.title }}
      </template>

      <template #content>
        {{ modal.content }}
      </template>

      <template #footer>
        <SecondaryButton @click="closeModal">
          Cancel
        </SecondaryButton>

        <PrimaryButton
          class="ml-3"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
          @click="modalProcess(modal.action)"
        >
          {{ modal.submit }}
        </PrimaryButton>
      </template>
    </DialogModal>
  </AppLayout>
</template>
