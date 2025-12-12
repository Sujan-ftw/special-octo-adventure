import java.util.Scanner;

public class VotingEligibility {
    public static void main(String[] args) {

        Scanner sc = new Scanner(System.in);

        System.out.print("Enter your age: ");
        int age = sc.nextInt();

        // Using switch case
        switch (age >= 18 ? 1 : 0) {
            case 1:
                System.out.println("You are eligible to vote.");
                break;
            case 0:
                System.out.println("You are not eligible to vote.");
                break;
        }

        sc.close();
    }
}

