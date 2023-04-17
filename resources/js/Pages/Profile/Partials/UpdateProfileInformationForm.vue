<script setup>
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Badge from '@/Components/Badge.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import OTPInput from '@/Components/OTPInput.vue';

const props = defineProps({
    user: Object,
});

const form = useForm({
    _method: 'PUT',
    name: props.user.name,
    email: props.user.email,
    username: props.user.username,
    phone: props.user.phone,
    otp: null,
    photo: null,
});

const rateLimit = ref({
  otp: false
});

const verificationLinkSent = ref(null);
const verificationOTPSent = ref(null);
const otpRetry = ref(null);
const otpExpired = ref(null);
const timer = ref(null);
const otpInput = ref(null);
const countdown = ref(null);
const photoPreview = ref(null);
const photoInput = ref(null);

const updateProfileInformation = () => {
    if (photoInput.value) {
        form.photo = photoInput.value.files[0];
    }

    form.post(route('user-profile-information.update'), {
        errorBag: 'updateProfileInformation',
        preserveScroll: true,
        onSuccess: () => {
          clearPhotoFileInput();
          clearOTPInput();
        },
    });
};

const sendEmailVerification = () => {
    verificationLinkSent.value = true;
};

const sendPhoneVerification = () => {
    verificationOTPSent.value = true;
    otpExpired.value = false;
    otpRetry.value++;

    switch (otpRetry.value) {
      case 1:
        timer.value = 60;
        break;
      case 2:
        timer.value = 120;
        break;
      case 3:
        timer.value = 300;
        break;
      default:
        break;
    }
    requestOTP();

    if (otpRetry.value > 3) {
      rateLimit.value.otp = true;
    }
    startCountdown();
};

const requestOTP = () => {
    router.post(route('verification.phone.send'), {}, {
        headers: {
            'Accept': '*/*',
            'Content-Type': 'application/json',
        },
        preserveScroll: true,
        onError: (errors) => {
            console.log(errors);
        },
    });
};

const startCountdown = () => {
  let count = timer.value;
  let interval = setInterval(() => {
    if (count === 0) {
      otpExpired.value = true;
      clearInterval(interval);
    } else {
      if (countdown?.value) {
        countdown.value.innerText = count;
      }
      count--;
    }
  }, 1000)
}

const selectNewPhoto = () => {
    photoInput.value.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];

    if (! photo) return;

    const reader = new FileReader();

    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };

    reader.readAsDataURL(photo);
};

const deletePhoto = () => {
    router.delete(route('current-user-photo.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
            clearPhotoFileInput();
        },
    });
};

const clearPhotoFileInput = () => {
    if (photoInput.value?.value) {
        photoInput.value.value = null;
    }
};

const clearOTPInput = () => {
    if (form.otp !== null) {
        form.otp = null;
    }
};
</script>

<template>
  <FormSection @submitted="updateProfileInformation">
    <template #title>
      Profile Information
    </template>

    <template #description>
      Update your account's profile information and email address.
    </template>

    <template #form>
      <!-- Profile Photo -->
      <div
        v-if="$page.props.jetstream.managesProfilePhotos"
        class="col-span-6 sm:col-span-4"
      >
        <!-- Profile Photo File Input -->
        <input
          id="photo"
          ref="photoInput"
          type="file"
          class="hidden"
          :disabled="!$page.props.auth.user?.permissions.canUpdateProfileInformation"
          @change="updatePhotoPreview"
        >

        <InputLabel
          for="photo"
          value="Photo"
        />

        <!-- Current Profile Photo -->
        <div
          v-show="! photoPreview"
          class="mt-2"
        >
          <img
            :src="user.profile_photo_url"
            :alt="user.name"
            class="rounded-full h-20 w-20 object-cover"
          >
        </div>

        <!-- New Profile Photo Preview -->
        <div
          v-show="photoPreview"
          class="mt-2"
        >
          <span
            class="block rounded-full w-20 h-20 bg-cover bg-no-repeat bg-center"
            :style="'background-image: url(\'' + photoPreview + '\');'"
          />
        </div>

        <SecondaryButton
          class="mt-2 mr-2"
          type="button"
          :disabled="!$page.props.auth.user?.permissions.canUpdateProfileInformation"
          @click.prevent="selectNewPhoto"
        >
          Select A New Photo
        </SecondaryButton>

        <SecondaryButton
          v-if="user.profile_photo_path"
          type="button"
          class="mt-2"
          :disabled="!$page.props.auth.user?.permissions.canUpdateProfileInformation"
          @click.prevent="deletePhoto"
        >
          Remove Photo
        </SecondaryButton>

        <InputError
          :message="form.errors.photo"
          class="mt-2"
        />
      </div>

      <!-- Name -->
      <div class="col-span-6 sm:col-span-4">
        <InputLabel
          for="name"
          value="Name"
        />
        <TextInput
          id="name"
          v-model="form.name"
          type="text"
          class="mt-1 block w-full"
          autocomplete="name"
          :disabled="!$page.props.auth.user?.permissions.canUpdateProfileInformation"
        />
        <InputError
          :message="form.errors.name"
          class="mt-2"
        />
      </div>

      <!-- Email -->
      <div class="col-span-6 sm:col-span-4">
        <div class="flex items-center">
          <InputLabel
            for="email"
            value="Email"
          />
          <Badge
            v-if="$page.props.jetstream.hasEmailVerification && user.email_verified_at !== null"
            class="ml-2 badge-success"
            value="Verified"
          />
          <Badge
            v-else-if="$page.props.jetstream.hasEmailVerification && user.email_verified_at === null"
            class="ml-2 badge-warning"
            value="Unverified"
          />
        </div>
        <TextInput
          id="email"
          v-model="form.email"
          type="email"
          class="mt-1 block w-full"
          autocomplete="email"
          :disabled="!$page.props.auth.user?.permissions.canUpdateProfileInformation"
        />
        <InputError
          :message="form.errors.email"
          class="mt-2"
        />

        <div v-if="$page.props.jetstream.hasEmailVerification && user.email_verified_at === null">
          <p class="text-sm mt-2 dark:text-white">
            Your email address is unverified.

            <Link
              :href="route('verification.send')"
              method="post"
              as="button"
              class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
              :disabled="!$page.props.auth.user?.permissions.canUpdateProfileInformation"
              @click.prevent="sendEmailVerification"
            >
              Click here to re-send the verification email.
            </Link>
          </p>

          <div
            v-show="verificationLinkSent"
            class="mt-2 font-medium text-sm text-green-600 dark:text-green-400"
          >
            A new verification link has been sent to your email address.
          </div>
        </div>
      </div>

      <!-- Username -->
      <div class="col-span-6 sm:col-span-4">
        <InputLabel
          for="username"
          value="Username"
        />
        <TextInput
          id="username"
          v-model="form.username"
          type="text"
          class="mt-1 block w-full"
          autocomplete="username"
          :disabled="!$page.props.auth.user?.permissions.canUpdateProfileInformation"
        />
        <InputError
          :message="form.errors.username"
          class="mt-2"
        />
      </div>

      <!-- Phone -->
      <div class="form-control col-span-6 sm:col-span-4">
        <div class="flex items-center">
          <InputLabel
            for="phone"
            value="Phone"
          />
          <Badge
            v-if="$page.props.jetstream.hasPhoneVerification && user.phone_verified_at !== null"
            class="ml-2 badge-success"
            value="Verified"
          />
          <Badge
            v-else-if="$page.props.jetstream.hasPhoneVerification && user.phone_verified_at === null"
            class="ml-2 badge-warning"
            value="Unverified"
          />
        </div>
        <div class="flex items-center">
          <span
            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 mt-1 block p-3 py-2 border rounded-md shadow-sm rounded-e-none border-r-0"
          >
            +62
          </span>
          <TextInput
            id="phone"
            v-model="form.phone"
            type="tel"
            class="mt-1 block w-full rounded-s-none"
            autocomplete="phone"
            :disabled="!$page.props.auth.user?.permissions.canUpdateProfileInformation"
          />
        </div>
        <InputError
          :message="form.errors.phone"
          class="mt-2"
        />

        <div v-if="$page.props.jetstream.hasPhoneVerification && user.phone_verified_at === null">
          <p class="text-sm mt-2 dark:text-white">
            Your phone number is unverified.
          </p> 

          <div
            v-if="verificationOTPSent"
            class="mt-2"
          >
            <OTPInput
              ref="otpInput"
              v-model="form.otp"
              autocomplete="otp"
              :disabled="!$page.props.auth.user?.permissions.canUpdateProfileInformation"
            />
            <InputError
              :message="form.errors.otp"
              class="mt-2"
            />

            <p
              v-if="rateLimit.otp"
              class="mt-2 font-medium text-sm text-red-600 dark:text-red-400"
            >
              You have exceed the OTP Retry limit, please try again later.
            </p>
            <div v-else>
              <p
                v-if="otpExpired ?? true"
                class="mt-2 underline text-sm text-white-content hover:text-indigo-500 dark:hover:text-white"
                :disabled="!$page.props.auth.user?.permissions.canUpdateProfileInformation"
                @click.prevent="sendPhoneVerification"
              >
                Click here to re-send the phone number verification OTP.
              </p>
              <p
                v-else
                class="mt-2 font-medium text-sm text-success"
              >
                A new verification OTP has been sent to your phone number. Try again in <span ref="countdown">{{ timer }}</span> second(s).
              </p>
            </div>
          </div>

          <Link
            v-else
            href="#"
            :preserve-state="true"
            class="mt-2 underline text-sm text-white-content hover:text-indigo-500 dark:hover:text-white"
            :disabled="!$page.props.auth.user?.permissions.canUpdateProfileInformation"
            @click.prevent="sendPhoneVerification"
          >
            Click here to send the phone number verification OTP.
          </Link>
        </div>
      </div>
    </template>

    <template #actions>
      <ActionMessage
        :on="form.recentlySuccessful"
        class="mr-3"
      >
        Saved.
      </ActionMessage>

      <ActionMessage
        :on="!$page.props.auth.user?.permissions.canUpdateProfileInformation"
        class="mr-3"
      >
        You cannot update profile information.
      </ActionMessage>

      <PrimaryButton
        :class="{ 'opacity-25': form.processing || !$page.props.auth.user?.permissions.canUpdateProfileInformation }"
        :disabled="form.processing || !$page.props.auth.user?.permissions.canUpdateProfileInformation"
      >
        Save
      </PrimaryButton>
    </template>
  </FormSection>
</template>
