import { notarize } from '@electron/notarize';

export default async (context) => {
    if (process.platform !== 'darwin') {
        return;
    }

    if (context.packager.platform.name !== 'mac') {
        return;
    }

    const hasAppleCredentials = Boolean(
        process.env.NATIVEPHP_APPLE_ID &&
            process.env.NATIVEPHP_APPLE_ID_PASS &&
            process.env.NATIVEPHP_APPLE_TEAM_ID,
    );

    if (!hasAppleCredentials) {
        console.log('  • skipping notarization (NATIVEPHP_APPLE_* credentials not set)');
        return;
    }

    console.log('  • notarizing macOS application...');

    const appId = process.env.NATIVEPHP_APP_ID;
    const { appOutDir } = context;
    const appName = context.packager.appInfo.productFilename;

    await notarize({
        appBundleId: appId,
        appPath: `${appOutDir}/${appName}.app`,
        appleId: process.env.NATIVEPHP_APPLE_ID,
        appleIdPassword: process.env.NATIVEPHP_APPLE_ID_PASS,
        teamId: process.env.NATIVEPHP_APPLE_TEAM_ID,
        tool: 'notarytool',
    });

    console.log(`  • notarization complete for ${appId}`);
};
