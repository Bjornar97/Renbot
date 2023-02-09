<script setup lang="ts">
import route from "ziggy-js";
</script>

<template>
    <VLayout>
        <VAppBar>
            <VAppBarTitle>
                <div class="d-flex items-center">
                    <img
                        class="mr-2 moderator-icon"
                        src="../../images/icons/moderator.png"
                        alt="Moderator Icon"
                    />
                    Renbot - Moderators
                </div>
            </VAppBarTitle>
        </VAppBar>

        <VNavigationDrawer>
            <VList>
                <VListItem
                    title="Commands"
                    :active="route().current('commands.index')"
                    :href="route('commands.index')"
                ></VListItem>
            </VList>
        </VNavigationDrawer>

        <VMain>
            <slot></slot>
        </VMain>
    </VLayout>
</template>

<style>
.moderator-icon {
    object-fit: contain;
    width: 1.3rem;
}
</style>
